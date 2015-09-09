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

/**
 * Class SummitGeoLocatedLocation
 * @package models\summit
 */
class SummitGeoLocatedLocation extends SummitAbstractLocation
{
    protected $mtiClassType = 'concrete';

    protected $array_mappings = array
    (
        'ID'           => 'id',
        'Name'         => 'name:json_string',
        'Description'  => 'description:json_string',
        'ClassName'    => 'class_name',
        'LocationType' => 'location_type',
        'Address1' => 'address_1:json_string',
        'Address2' => 'address_2:json_string',
        'ZipCode'  => 'zip_code',
        'City'     => 'city:json_string',
        'State'    => 'state:json_string',
        'Country'  => 'country:json_string',
        'Lng'      => 'lng',
        'Lat'      => 'lat',
    );

}