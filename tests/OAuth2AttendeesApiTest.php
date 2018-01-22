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
            'expand'   => 'member,schedule,rsvp'
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

    public function testGetAttendeeByID($attendee_id = 12923){

        $params = [
            'id'          => 23,
            'attendee_id' => $attendee_id,
            'expand'      => 'member,schedule,tickets,groups,rsvp,all_affiliations'
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

        return $attendee;
    }

    public function testGetAttendeeByOrderID(){

        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+external_order_id',
            'filter'   => 'external_order_id==615528547',
            'expand'   => 'member,schedule,tickets,ticket_type,all_affiliations'
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

    public function testAddAttendee($member_id = 1){
        $params = [
            'id' => 23,
        ];

        $data = [
           'member_id' => $member_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitAttendeesApiController@addAttendee",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
        return $attendee;
    }

    public function testDeleteAttendee(){
        $attendee = $this->testAddAttendee(3);

        $params = [
            'id' => 23,
            'attendee_id' => $attendee->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitAttendeesApiController@deleteAttendee",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testUpdateAttendee(){
        $attendee = $this->testGetAttendeeByID(12642);

        $params = [
            'id' => 23,
            'attendee_id' => $attendee->id
        ];

        $data = [
            'member_id'          => $attendee->member->id,
            'share_contact_info' => true
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitAttendeesApiController@updateAttendee",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
        $attendee = json_decode($content);
        $this->assertTrue(!is_null($attendee));
        return $attendee;
    }

    public function testAddAttendeeTicket(){
        $params = [
            'id'          => 23,
            'attendee_id' => 12642
        ];

        $data = [
            'ticket_type_id'       => 50,
            'external_order_id'    => '617372932',
            'external_attendee_id' => '774078887',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitAttendeesApiController@addAttendeeTicket",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $ticket = json_decode($content);
        $this->assertTrue(!is_null($ticket));
        return $ticket;
    }

    public function testDeleteAttendeeTicket(){
        $params = [
            'id'          => 23,
            'attendee_id' => 12642,
            'ticket_id'   => 14161
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitAttendeesApiController@deleteAttendeeTicket",
            $params,
            [],
            [],
            [],
            $headers
        );

        $this->assertResponseStatus(204);
    }
}