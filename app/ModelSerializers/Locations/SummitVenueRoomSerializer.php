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
 * Class SummitVenueRoomSerializer
 * @package ModelSerializers\Locations
 */
final class SummitVenueRoomSerializer extends SummitAbstractLocationSerializer
{
    protected static $array_mappings = [
        'VenueId'           => 'venue_id:json_int',
        'FloorId'           => 'floor_id:json_int',
        'Capacity'          => 'capacity:json_int',
        'OverrideBlackouts' => 'override_blackouts:json_boolean',
    ];

    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $room   = $this->object;
        $values = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'floor': {
                        if($room->hasFloor()) {
                            unset($values['floor_id']);
                            $values['floor'] = SerializerRegistry::getInstance()->getSerializer($room->getFloor())->serialize();
                        }
                    }
                    break;
                    case 'venue': {
                        if($room->hasVenue()) {
                            unset($values['venue_id']);
                            $values['venue'] = SerializerRegistry::getInstance()->getSerializer($room->getVenue())->serialize();
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}