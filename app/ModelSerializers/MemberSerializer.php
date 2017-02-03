<?php namespace ModelSerializers;
/**
 * Copyright 2016 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Illuminate\Support\Facades\Config;
use models\main\Member;

/**
 * Class MemberSerializer
 * @package ModelSerializers
 */
final class MemberSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [

        'FirstName'       => 'first_name:json_string',
        'LastName'        => 'last_name:json_string',
        'Gender'          => 'gender:json_string',
        'Bio'             => 'bio:json_string',
        'LinkedInProfile' => 'linked_in:json_string',
        'IrcHandle'       => 'irc:json_string',
        'TwitterHandle'   => 'twitter:json_string',
    ];

    protected static $allowed_relations = [

        'groups',
        'groups_events',
        'feedback',
        'affiliations',
    ];

    private static $expand_group_events = [
        'type',
        'location',
        'sponsors',
        'track',
        'track_groups',
        'groups',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $member         = $this->object;
        if(!$member instanceof Member) return [];

        if(!count($relations)) $relations = $this->getAllowedRelations();

        $values           = parent::serialize($expand, $fields, $relations, $params);
        $values['pic']    = Config::get("server.assets_base_url", 'https://www.openstack.org/'). 'profile_images/members/'. $member->getId();
        $summit           = isset($params['summit'])? $params['summit'] :null;

        $speaker          = !is_null($summit)? $summit->getSpeakerByMember($member): null;
        $attendee         = !is_null($summit)? $summit->getAttendeeByMember($member): null;
        $groups_events    = !is_null($summit)? $summit->getGroupEventsFor($member): null;

        if(in_array('groups', $relations))
            $values['groups'] = $member->getGroupsIds();

        if(!is_null($speaker))
            $values['speaker_id'] = $speaker->getId();

        if(!is_null($attendee))
            $values['attendee_id'] = $attendee->getId();

        if(!is_null($groups_events) && in_array('groups_events', $relations)){
            $res = [];
            foreach ($groups_events as $group_event){
                $res[] = SerializerRegistry::getInstance()
                    ->getSerializer($group_event)
                    ->serialize(implode(',', self::$expand_group_events));
            }
            $values['groups_events'] = $res;
        }

        if(in_array('affiliations', $relations)){
            $res = [];
            foreach ($member->getAffiliations() as $affiliation){
                $res[] = SerializerRegistry::getInstance()
                    ->getSerializer($affiliation)
                    ->serialize('organization');
            }
            $values['affiliations'] = $res;
        }

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {

                    case 'attendee': {
                        if (!is_null($attendee))
                        {
                            unset($values['attendee_id']);
                            $values['attendee'] = SerializerRegistry::getInstance()->getSerializer($attendee)->serialize($expand,[],['none']);
                        }
                    }
                    break;
                    case 'speaker': {
                        if (!is_null($speaker))
                        {
                            unset($values['speaker_id']);
                            $values['speaker'] = SerializerRegistry::getInstance()->getSerializer($speaker)->serialize($expand,[],['none']);
                        }
                    }
                    break;
                    case 'feedback': {
                        if(!in_array('feedback', $relations)) break;
                        if(is_null($summit)) break;
                        $feedback = array();
                        foreach ($member->getFeedbackBySummit($summit) as $f) {
                            $feedback[] = SerializerRegistry::getInstance()->getSerializer($f)->serialize();
                        }
                        $values['feedback'] = $feedback;
                    }
                    break;
                    case 'groups': {
                        if(!in_array('groups', $relations)) break;
                        $groups = [];
                        unset($values['groups']);
                        foreach ($member->getGroups() as $g) {
                            $groups[] = SerializerRegistry::getInstance()->getSerializer($g)->serialize(null, [], ['none']);
                        }
                        $values['groups'] = $groups;
                    }
                    break;
                }
            }
        }

        return $values;
    }
}