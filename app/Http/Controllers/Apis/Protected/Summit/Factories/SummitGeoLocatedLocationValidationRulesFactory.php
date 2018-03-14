<?php namespace App\Http\Controllers;
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

/**
 * Class SummitGeoLocatedLocationValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitGeoLocatedLocationValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     */
    public static function build(array $data, $update = false){

        $rules = SummitAbstractLocationValidationRulesFactory::build($data, $update);

        if($update) {
             return array_merge([
                'address_1'        => 'sometimes|string',
                'address_2'        => 'sometimes|string',
                'zip_code'         => 'sometimes|string',
                'city'             => 'string|required_with:address_1',
                'state'            => 'string|required_with:address_1',
                'country'          => 'country_iso_alpha2_code|required_with:address_1',
                'website_url'      => 'sometimes|url',
                'lng'              => 'sometimes|geo_longitude|required_with:lat',
                'lat'              => 'sometimes|geo_latitude|required_with:lng',
                'display_on_site'  => 'sometimes|boolean',
                'details_page'     => 'sometimes|boolean',
                'location_message' => 'sometimes|string',
            ], $rules);
        }

        return array_merge([
                'address_1'        => 'string|required_without:lng,lat',
                'address_2'        => 'sometimes|string',
                'zip_code'         => 'sometimes|string',
                'city'             => 'string|required_without:lng,lat',
                'state'            => 'string|required_without:lng,lat',
                'country'          => 'country_iso_alpha2_code|required_without:lng,lat',
                'lng'              => 'geo_longitude|required_with:lat|required_without:address_1,city,state,country',
                'lat'              => 'geo_latitude|required_with:lng|required_without:address_1,city,state,country',
                'website_url'      => 'sometimes|url',
                'display_on_site'  => 'sometimes|boolean',
                'details_page'     => 'sometimes|boolean',
                'location_message' => 'sometimes|string',
        ], $rules);
    }
}