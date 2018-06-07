<?php namespace App\Http\Utils;
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
/**
 * Class DateUtils
 * @package App\Http\Utils
 */
final class DateUtils
{
    /**
     * @param DateTime $start1
     * @param DateTime $end1
     * @param DateTime $start2
     * @param DateTime $end2
     * @return bool
     */
    public static function checkTimeFramesOverlap(DateTime $start1, DateTime $end1, DateTime $start2, DateTime $end2){
        return $start1 <= $end2 && $end1 >= $start2;
    }
}