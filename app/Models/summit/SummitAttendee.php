<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace models\summit;

use libs\utils\JsonUtils;
use models\utils\SilverstripeBaseModel;

/**
 * Class SummitAttendee
 * @package models\summit
 */
class SummitAttendee extends SilverstripeBaseModel
{
    protected $table = 'SummitAttendee';

    protected $array_mappings = array
    (
        'ID'                      => 'id:json_int',
        'SummitHallCheckedIn'     => 'summit_hall_checked_in:json_boolean',
        'SummitHallCheckedInDate' => 'summit_hall_checked_in_date:datetime_epoch',
        'SharedContactInfo'       => 'shared_contact_info:json_boolean',
        'TicketTypeID'            => 'ticket_type_id:json_int',
        'MemberID'                => 'member_id:json_int',

    );

    /**
     * @return SummitEvent[]
     */
    public function schedule()
    {
        $res =  $this->belongsToMany
        (
            'models\summit\SummitEvent',
            'SummitAttendee_Schedule',
            'SummitAttendeeID',
            'SummitEventID'
        )->withPivot('IsCheckedIn')->get();

        $events = array();
        foreach($res as $e)
        {
            $class = 'models\\summit\\'.$e->ClassName;
            $entity = $class::find($e->ID);
            $entity->attributes['IsCheckedIn'] = $e->pivot->IsCheckedIn;
            array_push($events, $entity);
        }
        return $events;
    }

    public function add2Schedule(SummitEvent $event)
    {
        if($this->isOnSchedule($event)) return false;
        $this->belongsToMany
        (
            'models\summit\SummitEvent',
            'SummitAttendee_Schedule',
            'SummitAttendeeID',
            'SummitEventID'
        )->attach($event->ID,['IsCheckedIn' => false] );
        return true;
    }

    public function removeFromSchedule(SummitEvent $event)
    {
        if(!$this->isOnSchedule($event)) return false;
        $this->belongsToMany
        (
            'models\summit\SummitEvent',
            'SummitAttendee_Schedule',
            'SummitAttendeeID',
            'SummitEventID'
        )->detach($event->ID);
        return true;
    }

    public function isOnSchedule(SummitEvent $event)
    {
        return $this->belongsToMany
        (
            'models\summit\SummitEvent',
            'SummitAttendee_Schedule',
            'SummitAttendeeID',
            'SummitEventID'
        )->where('SummitEventID', '=', $event->ID)->count() > 0;
    }

    public function checkIn(SummitEvent $event)
    {
        if(!$this->isOnSchedule($event)) return false;
        $this->belongsToMany
        (
            'models\summit\SummitEvent',
            'SummitAttendee_Schedule',
            'SummitAttendeeID',
            'SummitEventID'
        )->withPivot('IsCheckedIn')->updateExistingPivot($event->ID, ['IsCheckedIn' => true]);
        return true;
    }

    /**
     * @return Member
     */
    public function member()
    {
        return $this->hasOne('models\main\Member', 'ID', 'MemberID')->first();
    }

    /**
     * @return SummitTicketType
     */
    public function ticket_type()
    {
        return $this->hasOne('models\summit\SummitTicketType', 'ID', 'TicketTypeID')->first();
    }

    public function toArray()
    {
        $values = parent::toArray();
        $member = $this->member();

        $values['first_name'] = JsonUtils::toJsonString($member->FirstName);
        $values['last_name']  = JsonUtils::toJsonString($member->Surname);
        $values['gender']     = $member->Gender;

        if($this->SharedContactInfo)
        {
            $values['email']        = $member->Email;
            $values['second_email'] = $member->SecondEmail;
            $values['third_email']  = $member->ThirdEmail;
            $values['linked_in']    = $member->LinkedInProfile;
            $values['irc']          = $member->IRCHandle;
            $values['twitter']      = $member->TwitterName;
        }
        return $values;
    }

}