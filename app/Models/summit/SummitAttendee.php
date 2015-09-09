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
        'ID'                      => 'id',
        'SummitHallCheckedIn'     => 'summit_hall_checked_in',
        'SummitHallCheckedInDate' => 'summit_hall_checked_in_date:datetime_epoch',
        'SharedContactInfo'       => 'shared_contact_info',
        'TicketTypeID'            => 'ticket_type_id',
        'MemberID'                => 'member_id',
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
            array_push($events, $entity);
        }
        return $events;
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