<?php namespace models\summit;
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
use DateTime;
/**
 * Class SummitScheduleEmptySpot
 * @package models\summit
 */
final class SummitScheduleEmptySpot
{
    /**
     * @var int
     */
    private $location_id;

    /**
     * @var DateTime
     */
    private $start_date_time;

    /**
     * @var DateTime
     */
    private $end_date_time;

    /**
     * SummitScheduleEmptySpot constructor.
     * @param int $location_id
     * @param DateTime $start_date_time
     * @param DateTime $end_date_time
     */
    public function __construct($location_id, DateTime $start_date_time, DateTime $end_date_time)
    {
        $this->location_id     = $location_id;
        $this->start_date_time = $start_date_time;
        $this->end_date_time   = $end_date_time;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * @return DateTime
     */
    public function getStartDateTime()
    {
        return $this->start_date_time;
    }

    /**
     * @return DateTime
     */
    public function getEndDateTime()
    {
        return $this->end_date_time;
    }

    /**
     * @return int
     */
    public function getTotalMinutes(){
        $interval       = $this->end_date_time->diff($this->start_date_time);
        $total_minutes  = $interval->days * 24 * 60;
        $total_minutes += $interval->h * 60;
        $total_minutes += $interval->i;
        return intval($total_minutes);
    }
}