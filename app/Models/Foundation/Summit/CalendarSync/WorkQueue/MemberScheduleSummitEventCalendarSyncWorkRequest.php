<?php namespace models\summit\CalendarSync\WorkQueue;
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

use Doctrine\ORM\Mapping AS ORM;
use models\main\Member;
use models\summit\CalendarSync\CalendarSyncInfo;

/**
 * Class MemberScheduleSummitEventCalendarSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="MemberScheduleSummitEventCalendarSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
class MemberScheduleSummitEventCalendarSyncWorkRequest
extends ScheduleSummitEventCalendarSyncWorkRequest
{
    /**
    * @ORM\ManyToOne(targetEntity="models\main\Member", cascade={"persist"})
    * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
    * @var Member
    */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\CalendarSync\CalendarSyncInfo", cascade={"persist"})
     * @ORM\JoinColumn(name="CalendarID", referencedColumnName="ID")
     * @var CalendarSyncInfo
     */
    private $calendar;

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return CalendarSyncInfo
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * @param CalendarSyncInfo $calendar
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }
}