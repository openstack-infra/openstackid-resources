<?php namespace models\summit;

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

use Doctrine\ORM\Mapping AS ORM;
use models\utils\IEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAttendee_Schedule")
 * Class SummitAttendeeSchedule
 * @package models\summit
 */
class SummitAttendeeSchedule implements IEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ID", type="integer", unique=true, nullable=false)
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SummitAttendee
     */
    public function getAttendee()
    {
        return $this->attendee;
    }

    /**
     * @param SummitAttendee $attendee
     */
    public function setAttendee($attendee)
    {
        $this->attendee = $attendee;
    }

    public function clearAttendee(){
        $this->attendee = null;
        $this->event    = null;
    }

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

    /**
     * @return boolean
     */
    public function isIsCheckedIn()
    {
        return $this->is_checked_in;
    }

    /**
     * @param boolean $is_checked_in
     */
    public function setIsCheckedIn($is_checked_in)
    {
        $this->is_checked_in = $is_checked_in;
    }

    /**
     * @return string
     */
    public function getGoogleCalendarEventId()
    {
        return $this->google_calendar_event_id;
    }

    /**
     * @param string $google_calendar_event_id
     */
    public function setGoogleCalendarEventId($google_calendar_event_id)
    {
        $this->google_calendar_event_id = $google_calendar_event_id;
    }

    /**
     * @return string
     */
    public function getAppleCalendarEventId()
    {
        return $this->apple_calendar_event_id;
    }

    /**
     * @param string $apple_calendar_event_id
     */
    public function setAppleCalendarEventId($apple_calendar_event_id)
    {
        $this->apple_calendar_event_id = $apple_calendar_event_id;
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="SummitAttendee", inversedBy="schedule")
     * @ORM\JoinColumn(name="SummitAttendeeID", referencedColumnName="ID", nullable=true )
     * @var SummitAttendee
     */
    private $attendee;

    /**
     * @ORM\ManyToOne(targetEntity="SummitEvent")
     * @ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    private $event;

    /**
     * @ORM\Column(name="IsCheckedIn", type="boolean")
     * @var bool
     */
    private $is_checked_in;

    /**
     * @ORM\Column(name="GoogleCalEventId", type="integer")
     * @var string
     */
    private $google_calendar_event_id;

    /**
     * @ORM\Column(name="AppleCalEventId", type="boolean")
     * @var string
     */
    private $apple_calendar_event_id;
}