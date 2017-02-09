<?php namespace ModelSerializers;
/**
 * Copyright 2017 OpenStack Foundation
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
use models\main\Member;

/**
 * Class OwnMemberSerializer
 * @package ModelSerializers
 */
final class OwnMemberSerializer extends AbstractMemberSerializer
{

    protected static $allowed_relations = [
        'team_memberships',
        'groups_events',
        'favorite_summit_events'
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
        $summit           = isset($params['summit'])? $params['summit'] :null;
        $speaker          = !is_null($summit)? $summit->getSpeakerByMember($member): null;
        $attendee         = !is_null($summit)? $summit->getAttendeeByMember($member): null;
        $groups_events    = !is_null($summit)? $summit->getGroupEventsFor($member): null;

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

        if(in_array('team_memberships', $relations)){
            $res = [];
            foreach ($member->getTeamMemberships() as $team_membership){
                $res[] = SerializerRegistry::getInstance()
                    ->getSerializer($team_membership)
                    ->serialize('team,team.member');
            }
            $values['team_memberships'] = $res;
        }

        if(in_array('favorite_summit_events', $relations) && !is_null($summit)){
            $res = [];
            foreach ($member->getFavoritesEventsIds($summit) as $event_id){
                $res[] = intval($event_id);
            }
            $values['favorite_summit_events'] = $res;
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
                    case 'favorite_summit_events':{
                        if(!in_array('favorite_summit_events', $relations)) break;
                        if(is_null($summit)) break;
                        $favorites = [];
                        foreach ($member->getFavoritesSummitEvents($summit) as $events){
                            $favorites[] = SerializerRegistry::getInstance()
                                ->getSerializer($events)
                                ->serialize($expand);
                        }
                        $values['favorite_summit_events'] = $favorites;
                    }
                    break;
                }
            }
        }
        return $values;
    }
}