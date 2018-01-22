<?php namespace models\summit\factories;
/**
 * Copyright 2018 OpenStack Foundation
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
use models\summit\Summit;
use models\summit\SummitAttendee;
/**
 * Class SummitAttendeeFactory
 * @package models\summit\factories
 */
final class SummitAttendeeFactory
{
    /**
     * @param Summit $summit
     * @param Member $member
     * @param array $data
     * @return SummitAttendee
     */
    public static function build(Summit $summit, Member $member, array $data){
        return self::updateMainData($summit, new SummitAttendee, $member, $data);
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param Member $member
     * @param array $data
     * @return SummitAttendee
     */
    public static function updateMainData(Summit $summit, SummitAttendee $attendee, Member $member, array $data){
        $attendee->setMember($member);
        $attendee->setSummit($summit);

        if(isset($data['shared_contact_info']))
            $attendee->setShareContactInfo($data['shared_contact_info']);

        if(isset($data['summit_hall_checked_in']))
            $attendee->setSummitHallCheckedIn($data['summit_hall_checked_in']);

        if(isset($data['summit_hall_checked_in_date']))
            $attendee->setSummitHallCheckedInDate
            (
                new \DateTime(intval($data['summit_hall_checked_in_date']))
            );

        return $attendee;
    }
}