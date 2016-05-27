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

/**
 * Class MemberSerializer
 * @package ModelSerializers
 */
final class MemberSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'FirstName'       => 'first_name:json_string',
        'LastName'        => 'last_name:json_string',
        'Gender'          => 'gender:json_string',
        'Bio'             => 'bio:json_string',
        'LinkedInProfile' => 'linked_in:json_string',
        'IrcHandle'       => 'irc:json_string',
        'TwitterHandle'   => 'twitter:json_string',
    );


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
        $values         = parent::serialize($expand, $fields, $relations, $params);
        $values['pic']  = Config::get("server.assets_base_url", 'https://www.openstack.org/'). 'profile_images/members/'. $member->getId();
        $summit         = isset($params['summit'])? $params['summit'] :null;
        $speaker        = !is_null($summit)? $summit->getSpeakerByMember($member): null;
        $attendee       = !is_null($summit)? $summit->getAttendeeByMember($member): null;

        if(!is_null($speaker))
            $values['speaker_id'] = $speaker->getId();

        if(!is_null($attendee))
            $values['attendee_id'] = $attendee->getId();

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {

                    case 'attendee': {
                        if (!is_null($attendee))
                        {
                            unset($values['attendee_id']);
                            $values['attendee'] = SerializerRegistry::getInstance()->getSerializer($attendee)->serialize(null,[],['none']);
                        }
                    }
                    break;
                    case 'speaker': {
                        if (!is_null($speaker))
                        {
                            unset($values['speaker_id']);
                            $values['speaker'] = SerializerRegistry::getInstance()->getSerializer($speaker)->serialize(null,[],['none']);
                        }
                    }
                    break;
                    case 'feedback': {
                        $feedback = array();
                        foreach ($member->getFeedback() as $f) {
                            array_push($feedback,  SerializerRegistry::getInstance()->getSerializer($f)->serialize());
                        }
                        $values['feedback'] = $feedback;
                    }
                    break;
                }
            }
        }

        return $values;
    }
}