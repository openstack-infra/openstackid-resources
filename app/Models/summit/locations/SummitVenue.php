<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace models\summit;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitVenue")
 * Class SummitVenue
 * @package models\summit
 */
class SummitVenue extends SummitGeoLocatedLocation
{

    private $rooms = array();

    public function addRoom(SummitVenueRoom $room)
    {
        array_push($this->rooms, $room);
    }

    public function toArray()
    {
        $values = parent::toArray();

        $rooms = array();

        foreach($this->rooms as $r)
        {
            array_push($rooms, $r->toArray());
        }

        if(count($rooms) > 0)
            $values['rooms'] = $rooms;

        return $values;
    }

    /**
     * @return SummitVenueRoom[]
     */
    public function rooms()
    {
        return $this->hasMany('models\summit\SummitVenueRoom', 'VenueID', 'ID')->get();
    }
}