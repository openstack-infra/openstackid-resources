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
use models\summit\SummitEvent;

/**
 * Class ScheduleSummitEventCalendarSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="ScheduleSummitEventCalendarSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
class ScheduleSummitEventCalendarSyncWorkRequest
    extends AbstractCalendarSyncWorkRequest
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitEvent", cascade={"persist"})
     * @ORM\JoinColumn(name="ScheduleEventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    protected $event;

    /**
     * @return SummitEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param SummitEvent $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

}