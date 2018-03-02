<?php namespace App\Models\Foundation\Summit\Factories;
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
use models\summit\SummitAbstractLocation;
use models\summit\SummitAirport;
use models\summit\SummitExternalLocation;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitHotel;
use models\summit\SummitVenue;
/**
 * Class SummitLocationFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitLocationFactory
{
    /**
     * @param array $data
     * @return SummitAbstractLocation|null
     * @throws ValidationException
     */
    public static function build(array $data){
        if(!isset($data['class_name'])) throw new ValidationException("missing class_name param");
        $location = null;
        switch($data['class_name']){
            case SummitVenue::ClassName :{
                $location = self::populateSummitVenue(new SummitVenue, $data);
            }
            break;
            case SummitExternalLocation::ClassName :{
                $location = self::populateSummitExternalLocation(new SummitExternalLocation, $data);
            }
            break;
            case SummitHotel::ClassName :{
                $location = self::populateSummitHotel(new SummitHotel, $data);
            }
                break;
            case SummitAirport::ClassName :{
                $location = self::populateSummitAirport(new SummitAirport, $data);
            }
            break;
        }
        return $location;
    }

    /**
     * @param SummitAbstractLocation $location
     * @param array $data
     * @return SummitAbstractLocation
     */
    private static function populateSummitAbstractLocation(SummitAbstractLocation $location, array $data){
        if(isset($data['name']))
            $location->setName(trim($data['name']));

        if(isset($data['description']))
            $location->setDescription(trim($data['description']));

        return $location;
    }

    /**
     * @param SummitGeoLocatedLocation $location
     * @param array $data
     * @return SummitGeoLocatedLocation
     */
    private static function populateSummitGeoLocatedLocation(SummitGeoLocatedLocation $location, array $data){
        if(isset($data['address_1']))
            $location->setAddress1(trim($data['address_1']));

        if(isset($data['address_2']))
            $location->setAddress2(trim($data['address_2']));

        if(isset($data['zip_code']))
            $location->setZipCode(trim($data['zip_code']));

        if(isset($data['city']))
            $location->setCity(trim($data['city']));

        if(isset($data['state']))
            $location->setState(trim($data['state']));

        if(isset($data['country']))
            $location->setCountry(trim($data['country']));

        if(isset($data['website_url']))
            $location->setWebsiteUrl(trim($data['website_url']));

        if(isset($data['lng']))
            $location->setLng(trim($data['lng']));

        if(isset($data['lat']))
            $location->setLat(trim($data['lat']));

        if(isset($data['display_on_site']))
            $location->setDisplayOnSite(boolval($data['display_on_site']));

        if(isset($data['details_page']))
            $location->setDetailsPage(boolval($data['details_page']));

        if(isset($data['location_message']))
            $location->setLocationMessage(trim($data['location_message']));

        return $location;
    }

    /**
     * @param SummitVenue $venue
     * @param array $data
     * @return SummitVenue
     */
    public static function populateSummitVenue(SummitVenue $venue, array $data){
        self::populateSummitGeoLocatedLocation
        (
            self::populateSummitAbstractLocation($venue, $data),
            $data
        );

        if(isset($data['is_main']))
            $venue->setIsMain(boolval($data['is_main']));

        return $venue;
    }

    /**
     * @param SummitExternalLocation $external_location
     * @param array $data
     * @return SummitExternalLocation
     */
    public static function populateSummitExternalLocation(SummitExternalLocation $external_location, array $data){

        self::populateSummitGeoLocatedLocation
        (
            self::populateSummitAbstractLocation($external_location, $data),
            $data
        );

        if(isset($data['capacity']))
            $external_location->setCapacity(intval($data['capacity']));

        return $external_location;
    }

    /**
     * @param SummitHotel $hotel
     * @param array $data
     * @return SummitHotel
     */
    public static function populateSummitHotel(SummitHotel $hotel, array $data){

        self::populateSummitExternalLocation
        (
            self::populateSummitGeoLocatedLocation
            (
                self::populateSummitAbstractLocation($hotel, $data),
                $data
            ),
            $data
        );

        if(isset($data['hotel_type']))
            $hotel->setHotelType(trim($data['hotel_type']));

        if(isset($data['sold_out']))
            $hotel->setSoldOut(boolval($data['sold_out']));

        if(isset($data['booking_link']))
            $hotel->setBookingLink(trim($data['booking_link']));

        return $hotel;
    }

    /**
     * @param SummitAirport $airport
     * @param array $data
     * @return SummitAirport
     */
    public static function populateSummitAirport(SummitAirport $airport, array $data){

        self::populateSummitExternalLocation
        (
            self::populateSummitGeoLocatedLocation
            (
                self::populateSummitAbstractLocation($airport, $data),
                $data
            ),
            $data
        );

        if(isset($data['airport_type']))
            $airport->setAirportType(trim($data['airport_type']));

        return $airport;
    }

    /**
     * @param SummitAbstractLocation $location
     * @param array $data
     * @return SummitAbstractLocation
     */
    public static function populate(SummitAbstractLocation $location, array $data){
        if($location instanceof SummitVenue){
            return self::populateSummitVenue($location, $data);
        }
        if($location instanceof SummitHotel){
            return self::populateSummitHotel($location, $data);
        }
        if($location instanceof SummitAirport){
            return self::populateSummitAirport($location, $data);
        }
        if($location instanceof SummitExternalLocation){
            return self::populateSummitExternalLocation($location, $data);
        }
        return $location;
    }
}