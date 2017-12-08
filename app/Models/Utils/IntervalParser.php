<?php namespace App\Models\Utils;
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


/**
 * Class IntervalParser
 * @package App\Models\Utils
 */
final class IntervalParser
{
    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return array
     */
    public static function getInterval(\DateTime $from, \DateTime $to){
        $intervals  = [];
        $aux_from   = clone $from;
        $start_hour = intval($from->format('h'));
        $start_min  = intval($from->format('i'));

        do{
            $aux_to    = clone $aux_from;
            $aux_to->setTime(23, 59, 59);

            if($aux_to > $to){
                $aux_to = clone $to;
            }
            $intervals[] = [
              $aux_from,
              $aux_to
            ];
            $aux_from = clone $aux_from;
            $aux_from->add(new \DateInterval('P1D'));

        } while($aux_to < $to);

        return $intervals;
    }
}