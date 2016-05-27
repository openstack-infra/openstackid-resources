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
            'expand' => 'schedule,speakers' ,
            'id'     => 6
        );

        $headers  = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $start    = time();
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
        echo "execution call ".$delta." seconds ...";
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
        $this->assertTrue(count($summit->schedule) > 0);
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
            'id'              => 6,
            'page'            => 1,
            'per_page'        => 15,
            'filter'          => 'first_name=@John,last_name=@Bryce,email=@sebastian@',
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
            'id'           => 6,
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
            'id'          => 6,
            'attendee_id' => 1215
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

    public function testCurrentSummitMyAttendeeSchedule()
    {
        $params  = array
        (
            'id'          => 6,
            'attendee_id' => 'me'
        );

        $headers = array("HTTP_Authorization" => " Bearer " .$this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getAttendeeSchedule",
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

    public function testCurrentSummitMyAttendeeAddToSchedule($event_id = 7202, $summit_id = 6)
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
            'id'          => 6,
            'attendee_id' => 'me',
            'event_id'    => 7202
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
        $event_id = 7863;
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
            'id'          => 6,
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

    public function testCurrentSummitEventsWithFilter()
    {
        $params  = array
        (
            'id'     => 6,
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

    public function testCurrentSelectionMotiveSummitEvents()
    {
        $params  = array
        (
            'id'     => 6,
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
            'id'     => 6,
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
            'id'     => 6,
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
            'id'     => 6,
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
            'id'       => 6,
            'event_id' => 6838,
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


    public function testGetPublishedEventFields(){


         $params  = array
         (
             'id'        => 6,
             'event_id'  => 8900,
             'fields'    => 'id,avg_feedback_rate,head_count',
             'relations' => 'none'
         );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getScheduledEvent",
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

    public function testGetPublishedEventFieldsNotExists(){


        $params  = array
        (
            'id'        => 'current',
            'event_id'  => 8900,
            'fields'    => 'id_test',
            'relations' => 'none'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getScheduledEvent",
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

    public function testGetPublishedEvent(){

        $params  = array
        (
            'id'        => 'current',
            'event_id'  => 8900,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getScheduledEvent",
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
            'id'              => 6,
            'event_id'        => 9454,
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
            'from_date' => 1460148342,
            'limit'     => 100
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

    public function testGetEntityEventsFromCurrentSummitFromGivenDate()
    {
        $params  = array
        (
            'id'        => 'current',
            'from_date' => 1766620800,
            'limit'     => 100
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
            'last_event_id' => 62128,
            'limit' => 100
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

        $params  = array
        (
            'id'            => 6,
            'last_event_id' => 32795
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

    public function testGetEntityEventsFromCurrentSummitGreaterThanGivenIDMax()
    {
        $params  = array
        (
            'id'            => 6,
            'last_event_id' => PHP_INT_MAX,
            'limit' => 250,
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

        $params  = array
        (
            'id'            => 6,
            'last_event_id' => 32795
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
            'id'       => 6,
            'event_id' => 9454,
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
            'id'          => 6,
            'event_id'    => 9454,
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
            'location_id' => 25
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

    public function testGetCurrentSummitExternalOrder()
    {
        $params  = array
        (
            'id'        => 'current',
            'external_order_id' => 488240765
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
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

        $order  = json_decode($content);
        $this->assertTrue(!is_null($order));
    }

    public function testGetCurrentSummitExternalOrderNonExistent()
    {
        $params  = array
        (
            'id'        => 'current',
            'external_order_id' => 'ADDDD'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
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

        $order  = json_decode($content);
        $this->assertTrue(!is_null($order));
    }

    public function testGetCurrentSummitConfirmExternalOrder()
    {
        $params  = array
        (
            'id'                   => 'current',
            'external_order_id'    => 484446336,
            'external_attendee_id' => 611227262
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
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

        $attendee  = json_decode($content);
        $this->assertTrue(!is_null($attendee));
    }

    public function testCurrentSummitLocationEventsWithFilter()
    {
        $params  = array
        (
            'id'          => 6,
            'location_id' => 25,
            'filter'      => array
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
            "OAuth2SummitApiController@getLocationEvents",
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

    public function testCurrentSummitPublishedLocationEventsWithFilter()
    {
        $params  = array
        (
            'id'            => 'current',
            'location_id'   => 68,
            /*'filter'      => array
            (
                'speaker=@Alex',
            )*/
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE"       => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitApiController@getLocationPublishedEvents",
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


    public function testAddPresentationVideo()
    {
        $params  = array
        (
            'id'              => 6,
            'presentation_id' => 6838
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " .$this->access_token,
            "CONTENT_TYPE"       => "application/json"
        );

        $video_data = array
        (
            'you_tube_id' => 'nrGk0AuFd_9',
            'name'        => 'Fostering Full Equality, Organized by the Women of OpenStack!',
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

        $content = $response->getContent();
        $this->assertResponseStatus(201);

    }

}