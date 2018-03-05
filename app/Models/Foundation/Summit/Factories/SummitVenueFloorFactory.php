<?php namespace App\Models\Foundation\Summit\Factories;
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
use models\summit\SummitVenueFloor;
/**
 * Class SummitVenueFloorFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitVenueFloorFactory
{
    /**
     * @param array $data
     * @return SummitVenueFloor
     */
    static public function build(array $data){
        return self::populate(new SummitVenueFloor, $data);
    }

    /**
     * @param SummitVenueFloor $floor
     * @param array $data
     * @return SummitVenueFloor
     */
    static public function populate(SummitVenueFloor $floor, array $data){

        if(isset($data['name']))
            $floor->setName(trim($data['name']));

        if(isset($data['description']))
            $floor->setDescription(trim($data['description']));

        if(isset($data['number']))
            $floor->setNumber(intval($data['number']));

        return $floor;
    }
}