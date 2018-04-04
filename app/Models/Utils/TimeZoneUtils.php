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
use DateTimeZone;
/**
 * Class TimeZoneUtils
 * @package App\Models\Utils
 */
final class TimeZoneUtils
{

    /**
     * @param string $time_zone_id
     * @return DateTimeZone|null
     */
    public static function getTimeZoneById($time_zone_id){
        if (empty($time_zone_id)) return null;
        $time_zone_list = timezone_identifiers_list();
        if (isset($time_zone_list[$time_zone_id])) {
            $time_zone_name = $time_zone_list[$time_zone_id];
            return new DateTimeZone($time_zone_name);
        }
        return null;
    }
}