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
use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
/**
 * Class GoogleGeoCodingAPI
 * @see https://developers.google.com/maps/documentation/geocoding/
 * Users of the free API:
 * 2,500 requests per 24 hour period.
 * @package App\Services\Apis
 */
final class GoogleGeoCodingAPI implements IGeoCodingAPI
{

    const BaseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var Client
     */
    private $client;

    /**
     * GoogleGeoCodingAPI constructor.
     * @param string $api_key
     */
    public function __construct($api_key)
    {
        $this->api_key = $api_key;
        $this->client  = new Client();
    }

    /**
     * @param AddressInfo $address_info
     * @return GeoCoordinatesInfo
     * @throws GeoCodingApiException
     */
    public function getGeoCoordinates(AddressInfo $address_info)
    {

        list($address1, $address2) = $address_info->getAddress();
        $address = $address1 . ' ' . $address2;
        $city = $address_info->getCity();
        $state = $address_info->getState();
        if (!empty($city)) {
            $address .= ", {$city}";
        }
        if (!empty($state)) {
            $address .= ", {$state}";
        }
        $zip_code = $address_info->getZipCode();
        $country  = $address_info->getCountry();

        $formatted_city = urlencode($city);
        $components     = "locality:{$formatted_city}|country:{$country}";
        $params         = [];
        if (!empty($state)) {
            $formatted_state = urlencode($state);
            $components .= "|administrative_area:{$formatted_state}";
        }
        if (!empty($address)) {
            $formatted_address = urlencode($address);
            $components .= "|address:{$formatted_address}";
        }
        $params['components'] = $components;

        if (!empty($zip_code)) {
            $params['postal_code'] = urlencode($zip_code);
        }

        $response = $this->doRequest($params);

        if($response['status'] != GoogleGeoCodingAPI::ResponseStatusOK){
            throw new GeoCodingApiException($response['status']);
        }

        return new GeoCoordinatesInfo
        (
            $response['results'][0]['geometry']['location']['lat'],
            $response['results'][0]['geometry']['location']['lng']
        );
    }

    /**
     * @param array $params
     * @return array
     * @throws Exception
     * @throws RequestException
     */
    private function doRequest(array $params){
        try {

            $query = [
                'key' => $this->api_key
            ];

            foreach ($params as $param => $value) {
                $query[$param] = $value;
            }

            $response = $this->client->get(self::BaseUrl, [
                    'query' => $query
                ]
            );

            if ($response->getStatusCode() !== 200)
                throw new Exception('invalid status code!');

            $content_type = $response->getHeaderLine('content-type');

            if (empty($content_type))
                throw new Exception('invalid content type!');

            if (!strstr($content_type, 'application/json') )
                throw new Exception('invalid content type!');

            $json = $response->getBody()->getContents();
            return json_decode($json, true);
        }
        catch(RequestException $ex){
            Log::warning($ex->getMessage());
            throw $ex;
        }
    }

    /**
     * @param GeoCoordinatesInfo $coordinates
     * @return AddressInfo
     * @throws GeoCodingApiException
     */
    public function getAddressInfo(GeoCoordinatesInfo $coordinates)
    {
        $params = [
            'latlng' => sprintf("%s,%s", $coordinates->getLat(), $coordinates->getLng())
        ];

        $response = $this->doRequest($params);

        if($response['status'] != IGeoCodingAPI::ResponseStatusOK){
            throw new GeoCodingApiException($response['status']);
        }

        $results        = $response['results'];
        $street_address = null;

        foreach($results as $result){
            $types = $result['types'];
            foreach($types as $type) {
                if ($type == 'street_address') {
                    $street_address = $result;
                    break;
                }
            }
            if(!is_null($street_address)) break;
        }

        if(is_null($street_address))
            throw new GeoCodingApiException(IGeoCodingAPI::ResponseStatusZeroResults);

        $components = [];

        foreach ($street_address['address_components'] as $component){
              foreach($component['types'] as $comp_type){
                  if($comp_type == 'street_number'){
                      $components['street_number'] = $component['long_name'];
                      break;
                  }
                  if($comp_type == 'route'){
                      $components['street_name'] = $component['long_name'];
                      break;
                  }
                  if($comp_type == 'locality'){
                      $components['city'] = $component['long_name'];
                      break;
                  }
                  if($comp_type == 'administrative_area_level_1'){
                      $components['state'] = $component['long_name'];
                      break;
                  }
                  if($comp_type == 'country'){
                      $components['country'] = $component['short_name'];
                      break;
                  }
                  if($comp_type == 'postal_code'){
                      $components['zip_code'] = $component['long_name'];
                      break;
                  }
                  if($comp_type == 'postal_code'){
                      $components['zip_code'] = $component['long_name'];
                      break;
                  }
              }
        }

        if(isset($components['street_name']) && isset($components['street_number'])){
            $components['address1'] = sprintf("%s %s", $components['street_name'], $components['street_number']);
        }

        return new AddressInfo
        (
            isset($components['address1']) ? $components['address1'] : '',
            isset($components['address2']) ? $components['address2'] : '',
            isset($components['zip_code']) ? $components['zip_code'] : '',
            isset($components['state']) ? $components['state'] : '',
            isset($components['city']) ? $components['city'] : '',
            isset($components['country']) ? $components['country'] : ''
        );
    }
}