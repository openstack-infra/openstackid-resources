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
class OAuth2SummitApiTest extends ProtectedApiTest
{
    public function testGetSummit()
    {

        $params  = array
        (
            'expand' => 'schedule' ,
            'id'     => 5
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
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

    public function testGetCurrentSummit()
    {

        $params  = array
        (
            'expand' => 'attendees,schedule' ,
            'id'     => 'current'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
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
        $summit  = json_decode($content);
        $this->assertTrue(!is_null($summit));
    }

    public function testGetCurrentSummitAttendees()
    {
        $params  = array
        (
            'id'              => 'current',
            'page'            => 1,
            'per_page'        => 15,
            'filter'          => array
                                (
                                    'first_name==sebastian,email=@smarcet',
                                )
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getAttendees",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendees  = json_decode($content);
        $this->assertTrue(!is_null($attendees));
    }

    public function testGetCurrentSummitSpeakers()
    {
        $params  = array
        (
            'id'              => 'current',
            'page'            => 1,
            'per_page'        => 15,
            'filter'          => 'first_name=@slack,last_name=@slack',
            'order'          => '+first_name,-last_name'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSpeakers",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speakers  = json_decode($content);
        $this->assertTrue(!is_null($speakers));
    }

    public function testCurrentSummitMyAttendeeFail404()
    {
        App::singleton('models\resource_server\IAccessTokenService', 'AccessTokenServiceStub2');

        $params  = array
        (
            'expand'       => 'schedule' ,
            'id'           => 'current',
            'attendee_id'  => 'me',
            'access_token' => $this->access_token
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getAttendee",
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
        $params  = array
        (
            'expand'      => 'schedule,ticket_type,speaker,feedback' ,
            'id'          => 'current',
            'attendee_id' => '1'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getAttendee",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendee  = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testCurrentSummitMyAttendeeAddToSchedule()
    {
        $params  = array
        (
            'id'          => 'current',
            'attendee_id' => 'me',
            'event_id'    => '3872'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "POST",
            "OAuth2SummitApiController@addEventToAttendeeSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(201);
    }

    public function testCurrentSummitMyAttendeeScheduleCheckIn()
    {
        $params  = array
        (
            'id'          => 'current',
            'attendee_id' => 'me',
            'event_id'    => '3872'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "PUT",
            "OAuth2SummitApiController@checkingAttendeeOnEvent",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testCurrentSummitMyAttendeeScheduleUnset()
    {
        $params  = array
        (
            'id'          => 'current',
            'attendee_id' => 'me',
            'event_id'    => '3872'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitApiController@removeEventToAttendeeSchedule",
            $params,
            array(),
            array(),
            array(),
            $headers
        );
        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetMySpeakerFromCurrentSummit(){

        $params  = array
        (
              'expand'      => 'presentations' ,
              'id'          => 'current',
              'speaker_id' => 'me'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getSpeaker",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speaker  = json_decode($content);
        $this->assertTrue(!is_null($speaker));
    }

    public function testAddFeedback2Speaker()
    {
        $params  = array
        (
            'id'              => 'current',
            'speaker_id'      => 476,
            'presentation_id' => 3872
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $feedback_data = array
        (
            'rate'     => 10,
            'note'     => 'you are the best, wow!',
            'owner_id' => 11624
        );


        $response = $this->action
        (
            "POST",
            "OAuth2SummitApiController@addSpeakerFeedback",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($feedback_data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

    }

    public function testCurrentSummitEvents()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'title=@Regis',
                'start_date>1445895000'
            )
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getEvents",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $events  = json_decode($content);
        $this->assertTrue(!is_null($events));
    }


    public function testGetEvent(){
        $params  = array
        (
            'id'       => 'current',
            'event_id' => 3874,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getEvent",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $events  = json_decode($content);
        $this->assertTrue(!is_null($events));
    }

    public function testAddFeedback2Event()
    {
        $params  = array
        (
            'id'              => 'current',
            'event_id'        => 3591,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $feedback_data = array
        (
            'rate'     => 10,
            'note'     => 'nice presentation, wow!',
            'owner_id' => 1
        );


        $response = $this->action
        (
            "POST",
            "OAuth2SummitApiController@addEventFeedback",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($feedback_data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

    }

    public function testGetEntityEventsFromCurrentSummit()
    {
        $params  = array
        (
            'id'        => 'current',
            'from_date' => '2015-09-22'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
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

        $events  = json_decode($content);
        $this->assertTrue(!is_null($events));
    }

    public function testGetEntityEventsFromCurrentSummitGreatherThanGivenID()
    {
        $params  = array
        (
            'id'            => 'current',
            'last_event_id' => '39'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
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

        $events  = json_decode($content);
        $this->assertTrue(!is_null($events));
    }


    public function testGetEventFeedback()
    {
        $this->testAddFeedback2Event();

        $params  = array
        (
            'id'       => 'current',
            'event_id' => 3591,
            'expand'   => 'owner'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getEventFeedback",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $feedback  = json_decode($content);
        $this->assertTrue(!is_null($feedback));
    }


    public function testGetCurrentSummitLocations()
    {
        $params  = array
        (
            'id'        => 'current',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getLocations",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations  = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

    public function testGetCurrentSummitLocation()
    {
        $params  = array
        (
            'id'        => 'current',
            'location_id' => 18
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getLocation",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $locations  = json_decode($content);
        $this->assertTrue(!is_null($locations));
    }

}