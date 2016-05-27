<?php namespace ModelSerializers;

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

use Illuminate\Support\Facades\Config;

/**
 * Class SummitSerializer
 * @package ModelSerializers
 */
final class SummitSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'                   => 'name:json_string',
        'BeginDate'              => 'start_date:datetime_epoch',
        'EndDate'                => 'end_date:datetime_epoch',
        'StartShowingVenuesDate' => 'start_showing_venues_date:datetime_epoch',
        'Active'                 => 'active:json_boolean',
    );

    /**
     * @param string $expand
     * @return array
     */
    public function serialize($expand = null)
    {
        $summit              = $this->object;
        $values              = parent::serialize();
        $time_zone_list      = timezone_identifiers_list();
        $time_zone_id        = $summit->getTimeZoneId();
        $values['time_zone'] = null;

        if(!empty($time_zone_id) && isset($time_zone_list[$time_zone_id]))
        {

            $time_zone_name           = $time_zone_list[$time_zone_id];
            $time_zone                = new \DateTimeZone($time_zone_name);
            $time_zone_info           = $time_zone->getLocation();
            $time_zone_info['name']   = $time_zone->getName();
            $now                      = new \DateTime("now", $time_zone);
            $time_zone_info['offset'] = $time_zone->getOffset($now);
            $values['time_zone']      = $time_zone_info;
        }

        $values['logo']               = ($summit->getLogo() !== null) ?
            Config::get("server.assets_base_url", 'https://www.openstack.org/').$summit->getLogo()->getFilename()
            : null;

        // summit types
        $summit_types = array();
        foreach ($summit->getSummitTypes() as $type) {
            array_push($summit_types, SerializerRegistry::getInstance()->getSerializer($type)->serialize());
        }
        $values['summit_types'] = $summit_types;
        // tickets
        $ticket_types = array();
        foreach ($summit->getTicketTypes() as $ticket) {
            array_push($ticket_types, SerializerRegistry::getInstance()->getSerializer($ticket)->serialize());
        }
        $values['ticket_types'] = $ticket_types;
        //locations
        $locations = array();
        foreach ($summit->getLocations() as $location) {
            array_push($locations, SerializerRegistry::getInstance()->getSerializer($location)->serialize());
        }
        $values['locations'] = $locations;

        if(!empty($expand)){

        }
        return $values;
    }


}