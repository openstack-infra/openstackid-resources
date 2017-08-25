<?php namespace App\ModelSerializers\Marketplace;
use ModelSerializers\SilverStripeSerializer;

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
 * Class OfficeSerializer
 * @package App\ModelSerializers\Marketplace
 */
final class OfficeSerializer extends SilverStripeSerializer
{
    /**
     * @var array
     */
    protected static $array_mappings = [
        'Address'  => 'address:json_string',
        'Address2' => 'address2:json_string',
        'State'    => 'state:json_string',
        'ZipCode'  => 'zip_code:json_string',
        'City'     => 'city:json_string',
        'Country'  => 'country:json_string',
        'Lat'      => 'lat:json_float',
        'Lng'      => 'lng:json_float',
    ];
}