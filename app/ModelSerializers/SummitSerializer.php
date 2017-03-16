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
use models\summit\Summit;

/**
 * Class SummitSerializer
 * @package ModelSerializers
 */
final class SummitSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'                     => 'name:json_string',
        'BeginDate'                => 'start_date:datetime_epoch',
        'EndDate'                  => 'end_date:datetime_epoch',
        'StartShowingVenuesDate'   => 'start_showing_venues_date:datetime_epoch',
        'Active'                   => 'active:json_boolean',
        'ScheduleDefaultStartDate' => 'schedule_start_date:datetime_epoch',
        'TypeId'                   => 'type_id:json_int' ,
        'DatesLabel'               => 'dates_label:json_string' ,
    );

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $summit              = $this->object;
        if(!$summit instanceof Summit) return [];
        $values              = parent::serialize($expand, $fields, $relations, $params);
        $time_zone_list      = timezone_identifiers_list();
        $time_zone_id        = $summit->getTimeZoneId();
        $values['time_zone'] = null;

        if (!empty($time_zone_id) && isset($time_zone_list[$time_zone_id])) {

            $time_zone_name = $time_zone_list[$time_zone_id];
            $time_zone = new \DateTimeZone($time_zone_name);
            $time_zone_info = $time_zone->getLocation();
            $time_zone_info['name'] = $time_zone->getName();
            $now = new \DateTime("now", $time_zone);
            $time_zone_info['offset'] = $time_zone->getOffset($now);
            $values['time_zone'] = $time_zone_info;
        }

        $values['logo'] = ($summit->hasLogo()) ?
            Config::get("server.assets_base_url", 'https://www.openstack.org/') . $summit->getLogo()->getFilename()
            : null;

        // pages info
        $main_page                             = $summit->getMainPage();
        $schedule_page                         = $summit->getSchedulePage();
        $values['page_url']                    = sprintf("%ssummit/%s", Config::get("server.assets_base_url", 'https://www.openstack.org/'), $main_page);
        $values['schedule_page_url']           = sprintf("%ssummit/%s/%s", Config::get("server.assets_base_url", 'https://www.openstack.org/'), $main_page, $schedule_page);
        $values['schedule_event_detail_url']   = sprintf("%ssummit/%s/%s/%s", Config::get("server.assets_base_url", 'https://www.openstack.org/'), $main_page, $schedule_page, 'events/:event_id/:event_title');

        // tickets
        $ticket_types = [];
        foreach ($summit->getTicketTypes() as $ticket) {
            $ticket_types[] = SerializerRegistry::getInstance()->getSerializer($ticket)->serialize();
        }
        $values['ticket_types'] = $ticket_types;

        //locations
        $locations = [];
        foreach ($summit->getLocations() as $location) {
            $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
        }
        $values['locations'] = $locations;

        // wifi connections
        $wifi_connections = [];
        foreach ($summit->getWifiConnections() as $wifi_connection) {
            $wifi_connections[] = SerializerRegistry::getInstance()->getSerializer($wifi_connection)->serialize();
        }
        $values['wifi_connections'] = $wifi_connections;

        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'schedule': {
                        $event_types = array();
                        foreach ($summit->getEventTypes() as $event_type) {
                            $event_types[] = SerializerRegistry::getInstance()->getSerializer($event_type)->serialize();
                        }
                        $values['event_types'] = $event_types;

                        $presentation_categories = array();
                        foreach ($summit->getPresentationCategories() as $cat) {
                            $presentation_categories[] = SerializerRegistry::getInstance()->getSerializer($cat)->serialize();
                        }
                        $values['tracks'] = $presentation_categories;

                        // track_groups
                        $track_groups = array();
                        foreach ($summit->getCategoryGroups() as $group) {
                            $track_groups[] = SerializerRegistry::getInstance()->getSerializer($group)->serialize();
                        }
                        $values['track_groups'] = $track_groups;

                        $schedule = array();
                        foreach ($summit->getScheduleEvents() as $event) {
                            $schedule[] = SerializerRegistry::getInstance()->getSerializer($event)->serialize();
                        }
                        $values['schedule'] = $schedule;

                        $sponsors = array();
                        foreach ($summit->getSponsors() as $company) {
                            $sponsors[] = SerializerRegistry::getInstance()->getSerializer($company)->serialize();
                        }
                        $values['sponsors'] = $sponsors;

                        $speakers = array();
                        foreach ($summit->getSpeakers() as $speaker) {
                            $speakers[] =
                                SerializerRegistry::getInstance()->getSerializer($speaker)->serialize
                                (
                                    null, [], [],
                                    [
                                        'summit_id' => $summit->getId(),
                                        'published' => true
                                    ]
                                );

                        }
                        $values['speakers'] = $speakers;
                    }
                    break;
                    case 'type':{
                        if(isset($values['type_id']))
                        {
                            unset($values['type_id']);
                            $values['type'] = $summit->hasType() ?
                                SerializerRegistry::getInstance()->getSerializer($summit->getType())->serialize() : null;
                        }
                    }
                    break;
                }
            }
        }
        $values['timestamp'] = time();
        return $values;
    }
}