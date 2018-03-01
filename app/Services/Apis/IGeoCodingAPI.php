<?php namespace App\Services\Apis;
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
 * Interface IGeoCodingAPI
 * @package App\Services\Apis
 */
interface IGeoCodingAPI
{
    /**
     * indicates that no errors occurred; the address was successfully parsed
     * and at least one geocode was returned.
     */
    const ResponseStatusOK = 'OK';

    /**
     * indicates that the geocode was successful but returned no results.
     * This may occur if the geocoder was passed a non-existent address
     */
    const ResponseStatusZeroResults = 'ZERO_RESULTS';

    /**
     * indicates that you are over your quota.
     */
    const ResponseStatusOverQueryLimit = 'OVER_QUERY_LIMIT';

    /**
     * indicates that your request was denied.
     */
    const ResponseStatusRequestDenied = 'REQUEST_DENIED';

    /**
     * generally indicates that the query (address, components or latlng) is missing.
     */
    const ResponseStatusInvalidRequest = 'INVALID_REQUEST';

    /**
     * indicates that the request could not be processed due to a server error.
     * The request may succeed if you try again.
     */
    const ResponseStatusUnknownError = 'UNKNOWN_ERROR';

    /**
     * @param AddressInfo $address_info
     * @return GeoCoordinatesInfo
     * @throws GeoCodingApiException
     */
    public function getGeoCoordinates(AddressInfo $address_info);

    /**
     * @param GeoCoordinatesInfo $coordinates
     * @return AddressInfo
     * @throws GeoCodingApiException
     */
    public function getAddressInfo(GeoCoordinatesInfo $coordinates);
}