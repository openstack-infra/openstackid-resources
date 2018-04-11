<?php namespace App\Models\Utils;

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
use DateTime;
use DateTimeZone;

/**
 * Trait TimeZoneEntity
 * @package App\Models\Utils
 */
trait TimeZoneEntity
{
    /**
     * @return string
     */
    public function getTimeZoneId()
    {
        return $this->time_zone_id;
    }

    /**
     * @param string $time_zone_id
     */
    public function setTimeZoneId($time_zone_id)
    {
        $this->time_zone_id = $time_zone_id;
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimeZone()
    {
        try {
            return new DateTimeZone($this->time_zone_id);
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param DateTime $value
     * @return null|DateTime
     */
    public function convertDateFromUTC2TimeZone(DateTime $value)
    {
        $time_zone = $this->getTimeZone();
        if (is_null($time_zone)) return null;

        $utc_timezone = new DateTimeZone("UTC");
        $timestamp = $value->format('Y-m-d H:i:s');
        $utc_date = new DateTime($timestamp, $utc_timezone);

        return $utc_date->setTimezone($time_zone);
    }

    /**
     * @param DateTime $value
     * @return null|DateTime
     */
    public function convertDateFromTimeZone2UTC(DateTime $value)
    {
        $time_zone = $this->getTimeZone();
        if (is_null($time_zone)) return null;

        $utc_timezone = new DateTimeZone("UTC");
        $timestamp = $value->format('Y-m-d H:i:s');
        $local_date = new DateTime($timestamp, $time_zone);
        return $local_date->setTimezone($utc_timezone);
    }

    /**
     * @return array
     */
    public function getTimezones()
    {
        $timezones_list = [];
        foreach (DateTimeZone::listIdentifiers() as $timezone_identifier) {
            $timezones_list[$timezone_identifier] = $timezone_identifier;
        }
        return $timezones_list;
    }
}