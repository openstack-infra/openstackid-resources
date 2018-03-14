<?php
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
 * Class OAuth2SummitLocationsApiTest
 */
final class OAuth2SummitLocationsApiTest extends ProtectedApiTest
{
    public function testGetFolder(){
        $service = \Illuminate\Support\Facades\App::make(\App\Services\Model\IFolderService::class);
        $folder  =    $service->findOrMake('summits/1/locations/292/maps');
    }

    public function testGetCurrentSummitLocations($summit_id = 23)
    {
        $params = [
            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 5,
            'order'    => '-order'
        ];

        $headers =
        [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocations",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetSummitLocationsOrderByName($summit_id = 22)
    {
        $params = [
            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 5,
            'order'    => 'name-'
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocations",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitLocationsMetadata($summit_id = 23)
    {
        $params = [
            'id' => $summit_id,
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getMetadata",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $metadata = json_decode($content);
        $this->assertTrue(!is_null($metadata));
    }

    public function testGetCurrentSummitLocationsByClassNameVenueORAirport($summit_id = 24)
    {
        $params = [
            'id'         => $summit_id,
            'page'       => 1,
            'per_page'   => 5,
            'filter'     => [
                'class_name=='.\models\summit\SummitVenue::ClassName.',class_name=='.\models\summit\SummitAirport::ClassName,
            ]
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocations",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitVenues()
    {
        $params = array
        (
            'id' => 'current',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getVenues",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitHotels()
    {
        $params = array
        (
            'id' => 'current',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getHotels",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitAirports()
    {
        $params = array
        (
            'id' => 'current',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getAirports",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitExternalLocations()
    {
        $params = array
        (
            'id' => 'current',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getExternalLocations",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitLocation()
    {
        $params = array
        (
            'id' => 'current',
            'location_id' => 25
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocation",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testCurrentSummitLocationEventsWithFilter($summit_id = 7)
    {
        $params = array
        (
            'id'          => $summit_id,
            'page'        => 1,
            'per_page'    => 50,
            'location_id' => 52,
            'filter'      => array
            (
                'tags=@Nova',
                'speaker=@Todd'
            )
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocationEvents",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $events = json_decode($content);
        $this->assertTrue(!is_null($events));
    }

    public function testCurrentSummitPublishedLocationEventsWithFilter()
    {
        $params = array
        (
            'id' => 23,
            'location_id' => 311,
            'filter' => [

                'start_date>=1451479955'
            ]
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocationPublishedEvents",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $events = json_decode($content);
        $this->assertTrue(!is_null($events));
    }

    public function testCurrentSummitPublishedLocationTBAEvents()
    {
        $params = array
        (
            'id'          => 23,
            'location_id' => "tba",
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocationPublishedEvents",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $events = json_decode($content);
        $this->assertTrue(!is_null($events));
    }

    public function testAddLocationWithoutClassName($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_location';
        $data = [
            'name'       => $name,
            'description' => 'test location',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }


    public function testAddLocationVenue($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_location';

        $data = [
            'name'        => $name,
            'address1'    => 'Nazar 612',
            'city'        => 'Lanus',
            'state'       => 'Buenos Aires',
            'country'     => 'Argentina',
            'class_name'  => \models\summit\SummitVenue::ClassName,
            'description' => 'test location',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

        $location = json_decode($content);
        $this->assertTrue(!is_null($location));
        return $location;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddLocationVenueLatLng($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_location';

        $data = [
            'name'        => $name,
            'lat'         => '-34.6994795',
            'lng'         => '-58.3920795',
            'class_name'  => \models\summit\SummitVenue::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

        $location = json_decode($content);
        $this->assertTrue(!is_null($location));
        return $location;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddLocationVenueLatLngInvalid($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_location';

        $data = [
            'name'        => $name,
            'lat'         => '-134.6994795',
            'lng'         => '-658.3920795',
            'class_name'  => \models\summit\SummitVenue::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddLocationHotelAddress($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_hotel';

        $data = [
            'name'        => $name,
            'address_1'   => 'H. de Malvinas 1724',
            'city'        => 'Lanus Este',
            'state'       => 'Buenos Aires',
            'country'     => 'AR',
            'zip_code'    => '1824',
            'class_name'  => \models\summit\SummitHotel::ClassName,
            'hotel_type'  => \models\summit\SummitHotel::HotelTypePrimary,
            'capacity'    => 200
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $location = json_decode($content);
        $this->assertTrue(!is_null($location));
        return $location;
    }

    public function testUpdateLocationHotelOrder($summit_id = 24){

        $hotel = $this->testAddLocationHotelAddress($summit_id);
        $new_order = 9;
        $params = [
            'id'          => $summit_id,
            'location_id' => $hotel->id
        ];

        $data = [
            'order' => $new_order,
            'class_name'  => \models\summit\SummitHotel::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitLocationsApiController@updateLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $location = json_decode($content);
        $this->assertTrue(!is_null($location));
        $this->assertTrue($location->order == $new_order);
        return $location;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testUpdateExistentLocation($summit_id = 23){

        $params = [
            'id'          => $summit_id,
            'location_id' => 292
        ];

        $data = [
            'class_name'  => \models\summit\SummitVenue::ClassName,
            'name' => 'Sydney Convention and Exhibition Centre Update!'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitLocationsApiController@updateLocation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $location = json_decode($content);
        $this->assertTrue(!is_null($location));
        return $location;
    }

    /**
     * @param int $summit_id
     */
    public function testDeleteNewlyCreatedHotel($summit_id = 24){

        $hotel = $this->testAddLocationHotelAddress($summit_id);
        $params = [
            'id'          => $summit_id,
            'location_id' => $hotel->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitLocationsApiController@deleteLocation",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    /**
     * @param int $summit_id
     * @param int $venue_id
     * @param int $number
     * @return mixed
     */
    public function testAddVenueFloor($summit_id = 23, $venue_id = 292, $number = null){

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id
        ];

        if(is_null($number))
            $number = rand(0,1000);

        $name       = str_random(16).'_floor';
        $data = [
           'name'        => $name,
           'description' => 'test floor',
           'number'      => $number
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addVenueFloor",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $floor = json_decode($content);
        $this->assertTrue(!is_null($floor));
        return $floor;
    }

    /**
     * @param int $summit_id
     * @param int $venue_id
     * @return mixed
     */
    public function testUpdateVenueFloor($summit_id = 23, $venue_id = 292){

        $floor = $this->testAddVenueFloor($summit_id, $venue_id, rand(0,1000));
        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'floor_id' => $floor->id
        ];

        $data = [
            'name' => 'test floor update',
            'description' => 'test floor update',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitLocationsApiController@updateVenueFloor",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $floor = json_decode($content);
        $this->assertTrue(!is_null($floor));
        return $floor;
    }

    /**
     * @param int $summit_id
     * @param int $venue_id
     */
    public function testDeleteVenueFloor($summit_id = 23, $venue_id = 292){

        $floor = $this->testAddVenueFloor($summit_id, $venue_id, rand(0,1000));

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'floor_id' => $floor->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitLocationsApiController@deleteVenueFloor",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    /**
     * @param int $summit_id
     * @param int $venue_id
     * @return mixed
     */
    public function testAddVenueRoom($summit_id = 23, $venue_id = 292){

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
        ];

        $name       = str_random(16).'_room';

        $data = [
            'name'        => $name,
            'description' => 'test room',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addVenueRoom",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $room = json_decode($content);
        $this->assertTrue(!is_null($room));
        return $room;
    }


    /**
     * @param int $summit_id
     * @param int $venue_id
     * @return mixed
     */
    public function testAddVenueRoomWithFloor($summit_id = 23, $venue_id = 292){

        $floor = $this->testAddVenueFloor($summit_id, $venue_id, rand(0,1000));

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'floor_id' => $floor->id
        ];

        $name       = str_random(16).'_room';

        $data = [
            'name'        => $name,
            'description' => 'test room',
        ];


        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addVenueFloorRoom",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $room = json_decode($content);
        $this->assertTrue(!is_null($room));
        return $room;
    }

    /**
     * @param int $summit_id
     * @param int $venue_id
     * @return mixed
     */
    public function testUpdateVenueRoomWithFloor($summit_id = 23, $venue_id = 292){

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'floor_id' => 22,
            'room_id'  => 307
        ];

        $data = [
            'description' => 'Pyrmont Theatre Update',
            'order'       => 2,
            'capacity'    => 1000,
            'floor_id'    => 23
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitLocationsApiController@updateVenueFloorRoom",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $room = json_decode($content);
        $this->assertTrue(!is_null($room));
        return $room;
    }

    /**
     * @param int $summit_id
     * @param int $venue_id
     * @return mixed
     */
    public function testDeleteExistentRoom($summit_id = 23, $venue_id = 292, $room_id = 307){

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'room_id'  => 333
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitLocationsApiController@deleteVenueRoom",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetFloorById($summit_id = 23, $venue_id = 292, $floor_id = 23){

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'floor_id' => $floor_id,
            'expand'   => 'rooms'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitLocationsApiController@getVenueFloor",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $floor = json_decode($content);
        $this->assertTrue(!is_null($floor));
        return $floor;
    }

    public function testGetVenueFloorRoomById($summit_id = 23, $venue_id = 292, $floor_id = 23, $room_id = 309){

        $params = [
            'id'       => $summit_id,
            'venue_id' => $venue_id,
            'floor_id' => $floor_id,
            'room_id'  => $room_id,
            'expand'   => 'floor,venue'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitLocationsApiController@getVenueFloorRoom",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $room = json_decode($content);
        $this->assertTrue(!is_null($room));
        return $room;
    }

    public function testAddLocationBanner($summit_id = 23, $location_id = 315){
        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id
        ];

        $data = [
            'title'      => str_random(16).'_banner_title',
            'content'    => '<span>title</span>',
            'type'       => \App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner::TypePrimary,
            'enabled'    => true,
            'class_name' => \App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocationBanner",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $banner = json_decode($content);
        $this->assertTrue(!is_null($banner));
        return $banner;
    }


    public function testAddLocationScheduleBanner($summit_id = 23, $location_id = 315){
        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id
        ];

        $data = [
            'title'      => str_random(16).'_banner_title',
            'content'    => '<span>title</span>',
            'type'       => \App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner::TypePrimary,
            'enabled'    => true,
            'class_name' => \App\Models\Foundation\Summit\Locations\Banners\ScheduledSummitLocationBanner::ClassName,
            'start_date' => 1509876000,
            'end_date'   => (1509876000+1000),
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitLocationsApiController@addLocationBanner",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $banner = json_decode($content);
        $this->assertTrue(!is_null($banner));
        return $banner;
    }

    public function testGetLocationBanners($summit_id = 23, $location_id = 315)
    {
        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id,
            'page'        => 1,
            'per_page'    => 5,
            'order'       => '-id'
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocationBanners",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $banners = json_decode($content);
        $this->assertTrue(!is_null($banners));

        return $banners;
    }

    public function testGetLocationBannersFilterByClassName($summit_id = 23, $location_id = 315)
    {
        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id,
            'page'        => 1,
            'per_page'    => 5,
            'order'       => '-id',
            'filter'      => 'class_name=='.\App\Models\Foundation\Summit\Locations\Banners\ScheduledSummitLocationBanner::ClassName
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocationBanners",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $banners = json_decode($content);
        $this->assertTrue(!is_null($banners));
    }

    public function testGetLocationBannersFilterByInvalidClassName($summit_id = 23, $location_id = 315)
    {
        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id,
            'page'        => 1,
            'per_page'    => 5,
            'order'       => '-id',
            'filter'      => 'class_name==test,class_name==test2'
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitLocationsApiController@getLocationBanners",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $banners = json_decode($content);
        $this->assertTrue(!is_null($banners));
    }

    public function testDeleteLocationBanner($summit_id = 23, $location_id = 315){
        $banners = $this->testGetLocationBanners($summit_id, $location_id);

        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id,
            'banner_id'   => $banners->data[0]->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitLocationsApiController@deleteLocationBanner",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testDeleteLocationMap($summit_id = 22, $location_id = 214, $map_id=30){

        $params = [
            'id'          => $summit_id,
            'location_id' => $location_id,
            'map_id'      => $map_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitLocationsApiController@deleteLocationMap",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }
}