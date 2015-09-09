<?php namespace services\model;

use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitEvent;
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
final class SummitService implements ISummitService
{

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @return bool
     * @throws \Exception
     */
    public function addEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        $event = $summit->getScheduleEvent($event_id);
        if(is_null($event)) throw new \Exception('event not found on summit!');
        return $attendee->add2Schedule($event);
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return bool
     * @throws \Exception
     */
    public function checkInAttendeeOnEvent(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        $event = $summit->getScheduleEvent($event_id);
        if(is_null($event)) throw new \Exception('event not found on summit!');
        return $attendee->checkIn($event);
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @return bool
     * @throws \Exception
     */
    public function removeEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        $event = $summit->getScheduleEvent($event_id);
        if(is_null($event)) throw new \Exception('event not found on summit!');
        return $attendee->removeFromSchedule($event);
    }
}