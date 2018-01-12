<?php
/**
 * Copyright 2017 OpenStack Foundation
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

class OAuth2AttendeesApiTest extends ProtectedApiTest
{
    public function testGetAttendees(){

        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+id',
            'filter'   => 'email=@jimmy',
            'expand'   => 'member,schedule'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getAttendeesBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendees = json_decode($content);
        $this->assertTrue(!is_null($attendees));
    }

    public function testGetOwnAttendee(){

        $params = [
            'id' => 23,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getOwnAttendee",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testGetAttendeeByID(){

        $params = [
            'id'          => 23,
            'attendee_id' => 12641,
            'expand'      => 'member,schedule,tickets,affiliations,groups'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getAttendee",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testGetAttendeeByOrderID(){

        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+external_order_id',
            'filter'   => 'external_order_id==615528547',
            'expand'   => 'member,schedule,tickets,ticket_type'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitAttendeesApiController@getAttendeesBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $attendees = json_decode($content);
        $this->assertTrue(!is_null($attendees));
    }
}