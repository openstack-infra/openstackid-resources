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
/**
 * Class MemberCalendarScheduleSummitActionSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="MemberCalendarScheduleSummitActionSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
class MemberCalendarScheduleSummitActionSyncWorkRequest
extends MemberScheduleSummitActionSyncWorkRequest
{
    const SubType = 'CALENDAR';

    /**
     * @ORM\Column(name="CalendarId", type="string")
     * @var string
     */
    private $calendar_id;

    /**
     * @ORM\Column(name="CalendarName", type="string")
     * @var string
     */
    private $calendar_name;

    /**
     * @ORM\Column(name="CalendarDescription", type="string")
     * @var string
     */
    private $calendar_description;

    /**
     * @return string
     */
    public function getCalendarId()
    {
        return $this->calendar_id;
    }

    /**
     * @param string $calendar_id
     */
    public function setCalendarId($calendar_id)
    {
        $this->calendar_id = $calendar_id;
    }

    /**
     * @return string
     */
    public function getCalendarName()
    {
        return $this->calendar_name;
    }

    /**
     * @param string $calendar_name
     */
    public function setCalendarName($calendar_name)
    {
        $this->calendar_name = $calendar_name;
    }

    /**
     * @return string
     */
    public function getCalendarDescription()
    {
        return $this->calendar_description;
    }

    /**
     * @param string $calendar_description
     */
    public function setCalendarDescription($calendar_description)
    {
        $this->calendar_description = $calendar_description;
    }

    /**
     * @return string
     */
    public function getSubType(){
        return self::SubType;
    }

}