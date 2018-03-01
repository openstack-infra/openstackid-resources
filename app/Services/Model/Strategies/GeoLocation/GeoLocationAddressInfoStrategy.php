<?php namespace App\Services\Model\Strategies\GeoLocation;
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
use App\Services\Apis\AddressInfo;
use App\Services\Apis\IGeoCodingAPI;
use models\summit\SummitGeoLocatedLocation;
/**
 * Class GeoLocationAddressInfoStrategy
 * @package App\Services\Model\Strategies\GeoLocation
 */
final class GeoLocationAddressInfoStrategy implements IGeoLocationStrategy
{

    /**
     * @param SummitGeoLocatedLocation $location
     * @param IGeoCodingAPI $geo_coding_api
     * @return SummitGeoLocatedLocation
     */
    public function doGeoLocation(SummitGeoLocatedLocation $location, IGeoCodingAPI $geo_coding_api)
    {
        $response = $geo_coding_api->getGeoCoordinates
        (
            new AddressInfo
            (
                $location->getAddress1(),
                $location->getAddress2(),
                $location->getZipCode(),
                $location->getState(),
                $location->getCity(),
                $location->getCountry()
            )
        );

        $location->setLat($response->getLat());
        $location->setLng($response->getLng());

        return $location;
    }
}