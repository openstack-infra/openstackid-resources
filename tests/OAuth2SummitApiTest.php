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


    public function testCurrentSummitMyAttendeeFail404()
    {
        App::singleton('models\resource_server\IAccessTokenService', 'AccessTokenServiceStub2');

        $params  = array
        (
            'expand' => 'schedule' ,
            'id'     => 'current',
            'attendee_id'     => 'me'
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
            'expand' => 'ticket_type,schedule' ,
            'id'     => 'current',
            'attendee_id'     => 'me'
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


}