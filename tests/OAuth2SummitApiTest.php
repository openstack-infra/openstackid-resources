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
use LaravelDoctrine\ORM\Facades\EntityManager;

/**
 * Class OAuth2SummitApiTest
 */
final class OAuth2SummitApiTest extends ProtectedApiTest
{

    public function testGetSummits()
    {

        $start = time();
        $params = ['relations'=>'none'];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSummits",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $summits = json_decode($content);
        $end   = time();
        $delta = $end - $start;
        echo "execution call " . $delta . " seconds ...";
        $this->assertTrue(!is_null($summits));
        $this->assertResponseStatus(200);
    }

    public function testGetAllSummits()
    {

        $start = time();
        $params = [
            'relations'=>'none',
            'expand'   => 'none',
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getAllSummits",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $summits = json_decode($content);
        $end   = time();
        $delta = $end - $start;
        echo "execution call " . $delta . " seconds ...";
        $this->assertTrue(!is_null($summits));
        $this->assertResponseStatus(200);
    }

    public function testGetSummit($summit_id = 25)
    {

        $params = [

            'expand' => 'schedule',
            'id'     => $summit_id
        ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $start = time();
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSummit",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $end   = time();
        $delta = $end - $start;
        echo "execution call " . $delta . " seconds ...";
        $content = $response->getContent();
        $summit = json_decode($content);
        $this->assertTrue(!is_null($summit));
        $this->assertResponseStatus(200);

        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSummit",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $summit  = json_decode($content);
        $this->assertTrue(!is_null($summit));
        $this->assertTrue(count($summit->schedule) > 0);
        $this->assertResponseStatus(200);
    }

    public function testAddSummitAlreadyExistsName(){
        $params = [
        ];

        $data = [
            'name'         => 'Vancouver, BC',
            'start_date'   => 1522853212,
            'end_date'     => 1542853212,
            'time_zone_id' => 'America/Argentina/Buenos_Aires',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitApiController@addSummit",
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

    public function testAddSummit(){
        $params = [
        ];
        $name        = str_random(16).'_summit';
        $data = [
            'name'         => $name,
            'start_date'   => 1522853212,
            'end_date'     => 1542853212,
            'time_zone_id' => 'America/Argentina/Buenos_Aires',
            'submission_begin_date' => null,
            'submission_end_date' => null,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitApiController@addSummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $summit = json_decode($content);
        $this->assertTrue(!is_null($summit));
        return $summit;
    }

    public function testUpdateSummitAlreadyActiveError(){
        $summit = $this->testAddSummit();
        $params = [
            'id' => $summit->id
        ];
        $data = [
             'active' => 1
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitApiController@updateSummit",
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

    public function testUpdateSummitTitle(){
        $summit = $this->testAddSummit();
        $params = [
            'id' => $summit->id
        ];
        $data = [
            'name' => $summit->name.' update!'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitApiController@updateSummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $summit = json_decode($content);
        $this->assertTrue(!is_null($summit));

        return $summit;
    }

    public function testDeleteSummit(){

        $summit = $this->testAddSummit();
        $params = [
            'id' => $summit->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitApiController@deleteSummit",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);

    }

    public function testGetSummitMin($summit_id = 23)
    {

        $params = array
        (
            'id'     => $summit_id,
            'expand' =>'event_types',
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $start = time();
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSummit",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $end   = time();
        $delta = $end - $start;
        echo "execution call " . $delta . " seconds ...";
        $content = $response->getContent();
        $summit = json_decode($content);
        $this->assertTrue(!is_null($summit));
        $this->assertResponseStatus(200);

        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSummit",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $summit  = json_decode($content);
        $this->assertTrue(!is_null($summit));
        $this->assertResponseStatus(200);
    }

    public function testGetCurrentSummit($summit_id = 23)
    {

        $params = array
        (
            'expand' => 'schedule',
            'id'     => $summit_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSummit",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $summit = json_decode($content);
        $this->assertTrue(!is_null($summit));
    }

    public function testGetCurrentSummitSpeakers()
    {
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 50,
            'order'    => '+first_name,-last_name'
        ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSpeakers",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speakers = json_decode($content);
        $this->assertTrue(!is_null($speakers));
    }

    public function testAllSpeakers()
    {
        $params = [

            'page'     => 1,
            'per_page' => 15,
            'filter'   => 'first_name=@John,last_name=@Bryce,email=@sebastian@',
            'order'    => '+first_name,-last_name'
        ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getAllSpeakers",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speakers = json_decode($content);
        $this->assertTrue(!is_null($speakers));
    }

    public function testCurrentSummitMyAttendeeFail404()
    {
        App::singleton('App\Models\ResourceServer\IAccessTokenService', 'AccessTokenServiceStub2');

        $params = array
        (
            'expand'       => 'schedule',
            'id'           => 6,
            'attendee_id'  => 'me',
            'access_token' => $this->access_token
        );

        $headers  = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getAttendee",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(404);
    }

    public function testCurrentSummitMyAttendeeOK()
    {
        $params = array
        (
            'expand' => 'schedule,ticket_type,speaker,feedback',
            'id' => 6,
            'attendee_id' => 1215
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getAttendee",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testCurrentSummitMyAttendeeSchedule()
    {
        $params = array
        (
            'id' => 22,
            'attendee_id' => 'me'
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getAttendeeSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testCurrentSummitMyAttendeeAddToSchedule($event_id = 18845, $summit_id = 22)
    {
        $params = array
        (
            'id'          => $summit_id,
            'attendee_id' => 'me',
            'event_id'    => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "POST",
            "OAuth2SummitAttendeesApiController@addEventToAttendeeSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(201);
    }

    public function testCurrentSummitMyAttendeeScheduleUnset($event_id = 18845, $summit_id = 22)
    {
        //$this->testCurrentSummitMyAttendeeAddToSchedule($event_id, $summit_id);
        $params = array
        (
            'id' => $summit_id,
            'attendee_id' => 'me',
            'event_id' => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitAttendeesApiController@removeEventFromAttendeeSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testCurrentSummitMyAttendeeScheduleUnRSVP($event_id = 18639, $summit_id = 22)
    {
        //$this->testCurrentSummitMyAttendeeAddToSchedule($event_id, $summit_id);
        $params = array
        (
            'id'          => $summit_id,
            'attendee_id' => 'me',
            'event_id'    => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitAttendeesApiController@deleteEventRSVP",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetMySpeakerFromCurrentSummit()
    {

        $params = array
        (
            'expand' => 'presentations',
            'id' => 6,
            'speaker_id' => 'me'
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSpeaker",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speaker = json_decode($content);
        $this->assertTrue(!is_null($speaker));
    }

    public function testAllEventsByEventType()
    {
        $params = array
        (
            'id' => 'current',
            'expand' => 'feedback',
            'filter' => array
            (
                'event_type_id==4',
                'summit_id==6',
            ),
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getAllEvents",
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

    public function testGetEntityEventsFromCurrentSummit()
    {
        //$this->testGetCurrentSummit(22);

        $params = array
        (
            'id'        => '22',
            'from_date' => 1460148342,
            'limit'     => 100
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getSummitEntityEvents",
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

    public function testGetEntityEventsFromCurrentSummitFromGivenDate()
    {
        $params = array
        (
            'id'        => 7,
            'from_date' => 1471565531,
            'limit'     => 100
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getSummitEntityEvents",
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

    public function testGetEntityEventsFromCurrentSummitGreaterThanGivenID($summit_id = 7, $last_event_id = 702471)
    {
        $params = array
        (
            'id'            => $summit_id,
            'last_event_id' => $last_event_id,
            'limit'         => 100
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getSummitEntityEvents",
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

        $params = array
        (
            'id'            => 6,
            'last_event_id' => 32795
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getSummitEntityEvents",
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

    public function testGetEntityEventsFromCurrentSummitGreaterThanGivenIDMax()
    {
        $params = array
        (
            'id' => 6,
            'last_event_id' => PHP_INT_MAX,
            'limit' => 250,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getSummitEntityEvents",
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

        $params = array
        (
            'id' => 6,
            'last_event_id' => 32795
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getSummitEntityEvents",
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

    public function testGetCurrentSummitExternalOrder()
    {
        $params = array
        (
            'id' => 6,
            'external_order_id' => 488240765
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getExternalOrder",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $order = json_decode($content);
        $this->assertTrue(!is_null($order));
    }

    public function testGetCurrentSummitExternalOrderNonExistent()
    {
        $params = array
        (
            'id' => 6,
            'external_order_id' => 'ADDDD'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getExternalOrder",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(404);

        $order = json_decode($content);
        $this->assertTrue(!is_null($order));
    }

    public function testCurrentSummitConfirmExternalOrder()
    {
        $params = array
        (
            'id' => 6,
            'external_order_id' => 488240765,
            'external_attendee_id' => 615935124
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitApiController@confirmExternalOrderAttendee",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testAddPresentationVideo($summit_id = 7, $presentation_id = 15404)
    {
        $params = array
        (
            'id' => $summit_id,
            'presentation_id' => $presentation_id
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $video_data = array
        (
            'you_tube_id' => 'cpHa7kSOur0',
            'name' => 'test video',
            'description' => 'test video',
            'display_on_site' => true,
        );

        $response = $this->action
        (
            "POST",
            "OAuth2PresentationApiController@addVideo",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($video_data)
        );

        $video_id = $response->getContent();
        $this->assertResponseStatus(201);
        return intval($video_id);
    }

    public function testUpdatePresentationVideo()
    {
        $video_id = $this->testAddPresentationVideo(7, 15404);

        $params = array
        (
            'id' => 7,
            'presentation_id' => 15404,
            'video_id' => $video_id
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $video_data = array
        (
            'you_tube_id' => 'cpHa7kSOur0',
            'name' => 'test video update',
        );

        $response = $this->action
        (
            "PUT",
            "OAuth2PresentationApiController@updateVideo",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($video_data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);

    }

    public function testGetPresentationVideos()
    {

        //$video_id = $this->testAddPresentationVideo(7, 15404);

        $params = array
        (
            'id' => 7,
            'presentation_id' => 15404,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2PresentationApiController@getPresentationVideos",
            $params,
            array(),
            array(),
            array(),
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

    }

    public function testDeletePresentationVideo()
    {
        $video_id = $this->testAddPresentationVideo(7, 15404);

        $params = array
        (
            'id' => 7,
            'presentation_id' => 15404,
            'video_id' => $video_id
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "DELETE",
            "OAuth2PresentationApiController@deleteVideo",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);

    }

    public function testAdd2Favorite($summit_id = 22, $event_id = 18719){
        $params = array
        (
            'id'          => $summit_id,
            'member_id'   => 'me',
            'event_id'    => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "POST",
            "OAuth2SummitMembersApiController@addEventToMemberFavorites",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(201);
    }

    public function testRemoveFromFavorites($summit_id = 22, $event_id = 18719){

         $params = array
         (
             'id'          => $summit_id,
             'member_id'   => 'me',
             'event_id'    => $event_id
         );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitMembersApiController@removeEventFromMemberFavorites",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetMyFavorites(){

          $params = [

              'member_id' => 'me',
              'id'        => 7,
          ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitMembersApiController@getMemberFavoritesSummitEvents",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $favorites = json_decode($content);
        $this->assertTrue(!is_null($favorites));
    }

    public function testGetMyMemberFromCurrentSummit()
    {

        $params = [

            'expand'    => 'attendee,speaker,feedback,groups,presentations',
            'member_id' => 'me',
            'id'        => 22,
        ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitMembersApiController@getMyMember",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $member = json_decode($content);
        $this->assertTrue(!is_null($member));
    }

    public function testCurrentSummitMyMemberFavorites()
    {
        $params = array
        (
            'id' => 22,
            'member_id' => 'me',
            'expand' => 'speakers',
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitMembersApiController@getMemberFavoritesSummitEvents",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $favorites = json_decode($content);
        $this->assertTrue(!is_null($favorites));
    }

    public function testCurrentSummitMemberAddToSchedule($event_id = 18845, $summit_id = 22)
    {
        $params = array
        (
            'id'        => $summit_id,
            'member_id' => 'me',
            'event_id'  => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "POST",
            "OAuth2SummitMembersApiController@addEventToMemberSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(201);
    }

    public function testCurrentSummitMemberScheduleUnset($event_id = 18845, $summit_id = 22)
    {
        $this->testCurrentSummitMemberAddToSchedule($event_id, $summit_id);
        $params = array
        (
            'id'        => $summit_id,
            'member_id' => 'me',
            'event_id'  => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitMembersApiController@removeEventFromMemberSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testCurrentSummitMyMemberScheduleUnRSVP($event_id = 18639, $summit_id = 22)
    {
        //$this->testCurrentSummitMyAttendeeAddToSchedule($event_id, $summit_id);
        $params = array
        (
            'id'          => $summit_id,
            'member_id' => 'me',
            'event_id'    => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitMembersApiController@deleteEventRSVP",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }



}