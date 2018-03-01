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
use models\summit\SummitHotel;
/**
 * Class SummitHotelValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitHotelValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     */
    public static function build(array $data, $update = false){

        $rules = SummitExternalLocationValidationRulesFactory::build($data, $update);

        return array_merge([
            'hotel_type'   => sprintf('sometimes|in:%s,%s',SummitHotel::HotelTypePrimary, SummitHotel::HotelTypeAlternate),
            'sold_out'     => 'sometimes|boolean',
            'booking_link' => 'sometimes|url'
        ], $rules);
    }
}