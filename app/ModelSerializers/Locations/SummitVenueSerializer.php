<?php namespace ModelSerializers\Locations;
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
use ModelSerializers\SerializerRegistry;


/**
 * Class SummitVenueSerializer
 * @package ModelSerializers\Locations
 */
final class SummitVenueSerializer extends SummitGeoLocatedLocationSerializer
{
    protected static $array_mappings = array
    (
        'IsMain' => 'is_main::json_boolean',
    );

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $values = parent::serialize($expand, $fields, $relations, $params);
        $venue  = $this->object;
        // rooms
        $rooms = array();
        foreach($venue->getRooms() as $room)
        {
            $rooms[] = SerializerRegistry::getInstance()->getSerializer($room)->serialize($expand, $fields, $relations, $params);
        }
        if(count($rooms) > 0)
            $values['rooms'] = $rooms;

        // floors
        $floors = array();
        foreach($venue->getFloors() as $floor)
        {
            $floors[] = SerializerRegistry::getInstance()->getSerializer($floor)->serialize($expand, $fields, $relations, $params);
        }
        if(count($floors) > 0)
            $values['floors'] = $floors;

        return $values;
    }

}