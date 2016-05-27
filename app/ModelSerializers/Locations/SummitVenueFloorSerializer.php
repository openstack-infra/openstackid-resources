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
use ModelSerializers\SilverStripeSerializer;
use Illuminate\Support\Facades\Config;


/**
 * Class SummitVenueFloorSerializer
 * @package ModelSerializers\Locations
 */
final class SummitVenueFloorSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'        => 'name:json_string',
        'Description' => 'description:json_string',
        'Number'      => 'number:json_int',
        'VenueId'     => 'venue_id:json_int',
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
        $floor  = $this->object;
        // floor image
        $values['image']               = ($floor->getImage() !== null) ?
            Config::get("server.assets_base_url", 'https://www.openstack.org/').$floor->getImage()->getFilename()
            : null;
        // rooms
        $rooms = array();

        foreach($floor->getRooms() as $room)
        {

            $rooms[] = strstr('rooms',$expand) === false ? intval($room->getId()) :
                SerializerRegistry::getInstance()->getSerializer($room)->serialize($expand, $fields, $relations, $params);
        }

        if(count($rooms) > 0)
            $values['rooms'] = $rooms;

        return $values;
    }
}