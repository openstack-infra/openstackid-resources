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

use libs\utils\JsonUtils;
use Illuminate\Support\Facades\Config;
use models\summit\SummitAttendee;

/**
 * Class SummitAttendeeSerializer
 * @package ModelSerializers
 */
final class SummitAttendeeSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'SummitHallCheckedIn'     => 'summit_hall_checked_in:json_boolean',
        'SummitHallCheckedInDate' => 'summit_hall_checked_in_date:datetime_epoch',
        'SharedContactInfo'       => 'shared_contact_info:json_boolean',
        'MemberId'                => 'member_id:json_int',
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
        if(!count($relations)) $relations = $this->getAllowedRelations();
        $attendee = $this->object;
        if(!$attendee instanceof SummitAttendee) return [];

        $summit   = $attendee->getSummit();
        $values   = parent::serialize($expand, $fields, $relations, $params);
        $member   = null;
        $speaker  = null;
        $schedule = [];

        foreach ($attendee->getScheduledEventsIds() as $event_id){
            $schedule[] = intval($event_id);
        }

        $values['schedule'] = $schedule;

        $tickets = array();
        foreach($attendee->getTickets() as $t)
        {
            if(!$t->hasTicketType()) continue;
            array_push($tickets, intval($t->getTicketType()->getId()));
        }
        $values['tickets'] = $tickets;

        if($attendee->hasMember())
        {
            $member               = $attendee->getMember();
            $values['member_id']  = $member->getId();
            $speaker              = $summit->getSpeakerByMember($member);
            if (!is_null($speaker)) {
                $values['speaker_id'] = intval($speaker->getId());
            }
        }

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'schedule': {
                        unset($values['schedule']);
                        $schedule = [];
                        foreach ($attendee->getSchedule() as $s) {
                            if(!$summit->isEventOnSchedule($s->getEvent()->getId())) continue;
                            $schedule[] = SerializerRegistry::getInstance()->getSerializer($s)->serialize();
                        }
                        $values['schedule'] = $schedule;
                    }
                    break;
                    case 'tickets': {
                        unset($values['tickets']);
                        $tickets = array();
                        foreach($attendee->getTickets() as $t)
                        {
                            array_push($tickets, SerializerRegistry::getInstance()->getSerializer($t)->serialize($expand));
                        }
                        $values['tickets'] = $tickets;
                    }
                    break;
                    case 'speaker': {
                        if (!is_null($speaker))
                        {
                            unset($values['speaker_id']);
                            $values['speaker'] = SerializerRegistry::getInstance()->getSerializer($speaker)->serialize();
                        }
                    }
                    break;
                    case 'member':{
                        if($attendee->hasMember())
                        {
                            unset($values['member_id']);
                            $values['member']    = SerializerRegistry::getInstance()->getSerializer($attendee->getMember())->serialize($expand);
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}