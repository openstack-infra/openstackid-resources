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

    public function testGetSummits()
    {

        $params  = array
        (

        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
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
        $summits  = json_decode($content);
        $this->assertTrue(!is_null($summits));
        $this->assertResponseStatus(200);

    }

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
            'expand' => 'schedule' ,
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
            'id'          => '6',
            'attendee_id' => '561'
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

    public function testCurrentSummitMyAttendeeAddToSchedule($event_id = 5476, $summit_id = 5)
    {
        $params  = array
        (
            'id'          => $summit_id,
            'attendee_id' => 'me',
            'event_id'    => $event_id
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
        $event_id = 8860;
        $summit_id = 6;
        $this->testCurrentSummitMyAttendeeAddToSchedule($event_id, $summit_id);
        $params  = array
        (
            'id'          => $summit_id,
            'attendee_id' => 'me',
            'event_id'    => $event_id
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "DELETE",
            "OAuth2SummitApiController@removeEventFromAttendeeSchedule",
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

    public function testCurrentSummitEventsWithFilter()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'tags=@design',
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

    public function testCurrentSummitEvents()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
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

    public function testCurrentSummitEventsBySummitType()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'summit_type_id==1',
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

    public function testCurrentSummitPublishedEventsBySummitType()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'summit_type_id==2',
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
            "OAuth2SummitApiController@getScheduledEvents",
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

    public function testCurrentSummitPublishedEventsSummitTypeDesign()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'location' ,
            'filter' => array
            (
                "summit_type_id==2",
                "tags=@Magnum"
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
            "OAuth2SummitApiController@getScheduledEvents",
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

    public function testCurrentSummitEventsBySummitTypeOR()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'summit_type_id==2,tags=@Trove',
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

    public function testCurrentSummitEventsBySummitTypeAND()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'summit_type_id==2',
                'tags=@Trove',
            ),
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

    public function testCurrentSummitEventsByEventType()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'event_type_id==4',
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

    public function testAllEventsByEventType()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback' ,
            'filter' => array
            (
                'event_type_id==4',
                'summit_id==6' ,
            ),
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
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

        $events  = json_decode($content);
        $this->assertTrue(!is_null($events));
    }

    public function testCurrentSummitEventsByEventTypeExpandLocation()
    {
        $params  = array
        (
            'id'     => 'current',
            'expand' => 'feedback,location' ,
            'filter' => array
            (
                'event_type_id==4',
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

    public function testPostEvent($start_date = 1461613958, $end_date = 1461613990 )
    {
        $params  = array
        (
            'id' => 6,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title'           => 'test event',
            'description'     => 'test event',
            //'location_id'     => 25,
            //'allow_feedback'  => true,
            //'start_date'      => $start_date,
            //'end_date'        => $end_date,
            'type_id'         => 2,
            'summit_types_id' => [2],
            //'tags'            => ['tag#1','tag#2' ]
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitApiController@addEvent",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $event   = json_decode($content);
        $this->assertTrue($event->id > 0);
        return $event;
    }

    public function testUpdateEvent()
    {
        $event = $this->testPostEvent();
        unset($event->summit_types);
        unset($event->tags);
        $params  = array
        (
            'id'       => 6,
            'event_id' => $event->id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $event->title .= ' update';


        $response = $this->action
        (
            "PUT",
            "OAuth2SummitApiController@updateEvent",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($event)
        );

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $event   = json_decode($content);
        $this->assertTrue($event->id > 0);
        return $event;

    }

    public function testPublishEvent($start_date = 1461685500, $end_date = 1461685800)
    {
        $event = $this->testPostEvent($start_date,$end_date );
        unset($event->summit_types);
        unset($event->tags);

        $params  = array
        (
            'id'       => 6,
            'event_id' => $event->id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "PUT",
            "OAuth2SummitApiController@publishEvent",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $this->assertResponseStatus(204);

        return $event;
    }

    public function testUnPublishEvent()
    {
        $event = $this->testPublishEvent(1461682800, 1461683700);

        $params  = array
        (
            'id'       => 6,
            'event_id' => $event->id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "DELETE",
            "OAuth2SummitApiController@unPublishEvent",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $this->assertResponseStatus(204);

        return $event;
    }

    public function testDeleteEvent()
    {
        $event = $this->testPostEvent();

        $params  = array
        (
            'id'       => 6,
            'event_id' => $event->id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "DELETE",
            "OAuth2SummitApiController@deleteEvent",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $this->assertResponseStatus(204);

        return $event;
    }

    public function testAddFeedback2Event()
    {
        $params  = array
        (
            'id'              => 5,
            'event_id'        => 4189,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $feedback_data = array
        (
            'rate'        => 10,
            'note'        => 'nice presentation, wow!',
            'attendee_id' => 'me'
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
            'from_date' => '1449152383'
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

    public function testGetEntityEventsFromCurrentSummitGreaterThanGivenID()
    {
        $params  = array
        (
            'id'            => 6,
            'last_event_id' => 0
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
            array('expand'   => 'owner'),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $feedback  = json_decode($content);
        $this->assertTrue(!is_null($feedback));
    }

    public function testGetMeEventFeedback()
    {
        $this->testAddFeedback2Event();

        $params  = array
        (
            'id'          => 'current',
            'event_id'    => 3591,
            'attendee_id' => 'me',
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
            array( 'expand' => 'owner'),
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