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
use models\exceptions\ValidationException;
use models\summit\SummitAirport;
use models\summit\SummitExternalLocation;
use models\summit\SummitHotel;
use models\summit\SummitVenue;
use models\summit\SummitVenueRoom;
/**
 * Class SummitLocationValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitLocationValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     * @throws ValidationException
     */
    public static function build(array $data, $update = false){

        if(!isset($data['class_name']))
            throw new ValidationException('class_name is not set');

        switch($data['class_name']){
            case SummitVenue::ClassName: {
                return SummitVenueValidationRulesFactory::build($data, $update);
            }
            break;
            case SummitAirport::ClassName: {
                return SummitAirportValidationRulesFactory::build($data, $update);
            }
            break;
            case SummitHotel::ClassName: {
                return SummitHotelValidationRulesFactory::build($data, $update);
            }
            break;
            case SummitExternalLocation::ClassName: {
                return SummitExternalLocationValidationRulesFactory::build($data, $update);
            }
            case SummitVenueRoom::ClassName: {
                return SummitVenueRoomValidationRulesFactory::build($data, $update);
            }
            break;
            default:{
                throw new ValidationException('invalid class_name param');
            }
            break;
        }
        return [];
    }
}