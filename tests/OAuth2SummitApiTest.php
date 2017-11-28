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
final class OAuth2SummitApiTest extends ProtectedApiTest
{

    public function testGetSummits()
    {

        $params = ['expand' => 'type'];

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
        $this->assertTrue(!is_null($summits));
        $this->assertResponseStatus(200);
    }

    public function testGetAllSummits()
    {

        $params = ['expand' => 'type'];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getAllSummits",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $summits = json_decode($content);
        $this->assertTrue(!is_null($summits));
        $this->assertResponseStatus(200);
    }

    public function testGetSummit($summit_id = 22)
    {

        $params = array
        (
            'expand' => 'schedule',
            'id'     => $summit_id
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
        $this->assertTrue(count($summit->schedule) > 0);
        $this->assertResponseStatus(200);
    }

    public function testGetTracks()
    {

        $params = array
        (
            'id' => 6,
            'expand' => 'track_groups',
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getTracks",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $tracks = json_decode($content);
        $this->assertTrue(!is_null($tracks));
        $this->assertResponseStatus(200);
    }

    public function testGetTrackGroups()
    {

        $params = array
        (
            'id' => 6,
            'expand' => 'tracks',
        );

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2SummitApiController@getTracksGroups",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $groups = json_decode($content);
        $this->assertTrue(!is_null($groups));
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

            'id'       => 'current',
            'page'     => 1,
            'per_page' => 15,
            'filter'   => 'first_name=@John,last_name=@Bryce,email=@sebastian@',
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

    public function testCurrentSummitEventsWithFilter()
    {
        $params = array
        (
            'id' => 6,
            'expand' => 'feedback',
            'filter' => array
            (
                'tags=@design',
                'start_date>1445895000'
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
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testCurrentSelectionMotiveSummitEvents()
    {
        $params = array
        (
            'id' => 6,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testCurrentSummitEventsBySummitType()
    {
        $params = array
        (
            'id' => 6,
            'expand' => 'feedback',
            'filter' => array
            (
                'summit_type_id==1',
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
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testCurrentSummitPublishedEventsBySummitType()
    {
        $params = array
        (
            'id' => 6,
            'expand' => 'feedback',
            'filter' => array
            (
                'summit_type_id==2',
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
            "OAuth2SummitEventsApiController@getScheduledEvents",
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

    public function testCurrentSummitPublishedEventsSummitTypeDesign()
    {
        $params = array
        (
            'id' => 6,
            'expand' => 'location',
            'filter' => array
            (
                "summit_type_id==2",
                "tags=@Magnum"
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
            "OAuth2SummitEventsApiController@getScheduledEvents",
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

    public function testCurrentSummitEventsBySummitTypeOR()
    {
        $params = array
        (
            'id' => 'current',
            'expand' => 'feedback',
            'filter' => array
            (
                'summit_type_id==2,tags=@Trove',
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
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testCurrentSummitEventsBySummitTypeAND()
    {
        $params = array
        (
            'id' => 'current',
            'expand' => 'feedback',
            'filter' => array
            (
                'summit_type_id==2',
                'tags=@Trove',
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
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testCurrentSummitEventsByEventType()
    {
        $params = array
        (
            'id' => 'current',
            'expand' => 'feedback',
            'filter' => array
            (
                'event_type_id==4',
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
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testCurrentSummitEventsByEventTypeExpandLocation($summit_id = 7)
    {
        $params = array
        (
            'id' => $summit_id,
            'expand' => 'feedback,location',
            'filter' => array
            (
                'event_type_id==91',
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
            "OAuth2SummitEventsApiController@getEvents",
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

    public function testGetEvent()
    {
        $params = array
        (
            'id' => 7,
            'event_id' => 15303,
            'expand' => 'speakers',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getEvent",
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

    public function testGetPublishedEventFields()
    {

        $params = array
        (
            'id' => 7,
            'event_id' => 17300,
            'fields' => 'id, avg_feedback_rate, head_count',
            'relations' => 'metrics'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getScheduledEvent",
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

    public function testGetPublishedEventFieldsNotExists()
    {


        $params = array
        (
            'id' => 6,
            'event_id' => 8900,
            'fields' => 'id_test',
            'relations' => 'none'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getScheduledEvent",
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

    public function testGetPublishedEvent()
    {

        $params = array
        (
            'id' => 6,
            'event_id' => 8900,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );


        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getScheduledEvent",
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

    public function testPostEvent($start_date = 1477645200, $end_date = 1477647600)
    {
        $params = array
        (
            'id' => 7,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title' => 'Neutron: tbd',
            'description' => 'TBD',
            'location_id' => 179,
            'allow_feedback' => true,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'type_id' => 95,
            'tags' => ['Neutron']
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitEventsApiController@addEvent",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $event = json_decode($content);
        $this->assertTrue($event->getId() > 0);
        return $event;
    }

    public function testPostPresentationFail412($start_date = 1461510000, $end_date = 1461513600)
    {
        $params = array
        (
            'id' => 7,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title' => 'test presentation BCN',
            'description' => 'test presentation BCN',
            'allow_feedback' => true,
            'type_id' => 86,
            'tags' => ['tag#1', 'tag#2']
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitEventsApiController@addEvent",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(412);
    }

    public function testPostPresentation($start_date = 1461510000, $end_date = 1461513600)
    {
        $params = array
        (
            'id' => 7,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title' => 'test presentation BCN',
            'description' => 'test presentation BCN',
            'allow_feedback' => true,
            'type_id' => 86,
            'tags' => ['tag#1', 'tag#2'],
            'speakers' => [1, 2, 3],
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitEventsApiController@addEvent",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);

        $content = $response->getContent();
        $presentation = json_decode($content);

        $this->assertTrue($presentation->getId() > 0);
        return $presentation;
    }

    public function testUpdateEvent()
    {
        /*$event = $this->testPostEvent();
        unset($event->tags);*/
        $params = array
        (
            'id' => 6,
            'event_id' => 15303,
        );

        $data = array
        (
            'tags' => ['keystone'],
        );


        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "PUT",
            "OAuth2SummitEventsApiController@updateEvent",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(200);
        $content = $response->getContent();
        $event = json_decode($content);
        $this->assertTrue($event->id > 0);
        return $event;

    }

    public function testPublishEvent($start_date = 1461520800, $end_date = 1461526200)
    {
        $event = $this->testPostEvent($start_date, $end_date);
        unset($event->tags);

        $params = array
        (
            'id' => 6,
            'event_id' => $event->getId(),
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "PUT",
            "OAuth2SummitEventsApiController@publishEvent",
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
        $event = $this->testPublishEvent(1461529800, 1461533400);

        $params = array
        (
            'id' => 6,
            'event_id' => $event->getId(),
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "DELETE",
            "OAuth2SummitEventsApiController@unPublishEvent",
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

        $params = array
        (
            'id' => 6,
            'event_id' => $event->getId(),
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "DELETE",
            "OAuth2SummitEventsApiController@deleteEvent",
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
        $params = array
        (
            'id' => 7,
            'event_id' => 17300,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $feedback_data = array
        (
            'rate' => 10,
            'note' => 'nice presentation, wow!',
            'attendee_id' => 'me'
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitEventsApiController@addEventFeedback",
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

    public function testAddFeedback2EventByMember($summit_id = 22, $event_id = 17683)
    {
        $params = array
        (
            'id'       => $summit_id,
            'event_id' => $event_id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $feedback_data = array
        (
            'rate' => 5,
        );

        $response = $this->action
        (
            "POST",
            "OAuth2SummitEventsApiController@addEventFeedbackByMember",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($feedback_data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);

    }

    public function testUpdateFeedback2EventByMember($summit_id = 22, $event_id = 17683)
    {
        //$this->testAddFeedback2EventByMember($summit_id, $event_id);
        $params = array
        (
            'id'       => $summit_id,
            'event_id' => $event_id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $feedback_data = array
        (
            'rate' => 3,
            'note' => 'update',
        );

        $response = $this->action
        (
            "PUT",
            "OAuth2SummitEventsApiController@updateEventFeedbackByMember",
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

    public function testGetEventFeedback()
    {
        //$this->testAddFeedback2Event();

        $params = array
        (
            'id' => 7,
            'event_id' => 17300,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getEventFeedback",
            $params,
            array('expand' => 'owner'),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $feedback = json_decode($content);
        $this->assertTrue(!is_null($feedback));

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getEventFeedback",
            $params,
            array('expand' => 'owner'),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $feedback = json_decode($content);
        $this->assertTrue(!is_null($feedback));
    }

    public function testGetMeEventFeedback()
    {
        $this->testAddFeedback2Event();

        $params = array
        (
            'id' => 6,
            'event_id' => 9454,
            'attendee_id' => 'me',
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getEventFeedback",
            $params,
            array('expand' => 'owner'),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $feedback = json_decode($content);
        $this->assertTrue(!is_null($feedback));
    }

    public function testGetCurrentSummitLocations()
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
            "OAuth2SummitLocationsApiController@getLocations",
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

    public function testCurrentSummitLocationEventsWithFilter($summit_id = 7)
    {
        $params = array
        (
            'id' => $summit_id,
            'page' => 1,
            'per_page' => 50,
            'location_id' => 52,
            'filter' => array
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

    public function testGetSummitNotifications()
    {

        $params = array
        (
            'id' => 7,
            'page' => 1,
            'per_page' => 15,
            'filter' => [
                'channel==' . \models\summit\SummitPushNotificationChannel::Event . ',channel==' . \models\summit\SummitPushNotificationChannel::Group,
            ],
            'order' => '+sent_date'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitNotificationsApiController@getAll",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $notifications = json_decode($content);
        $this->assertTrue(!is_null($notifications));
    }

    public function testGetAllScheduledEvents()
    {

        $params = array
        (
            'id' => 7,
            'page' => 1,
            'per_page' => 10,
            'filter' => array
            (
                'title=@Lightning',
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
            "OAuth2SummitEventsApiController@getScheduledEvents",
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

    public function testGetAllScheduledEventsUsingOrder()
    {

        $params = array
        (
            'id' => 7,
            'page' => 1,
            'per_page' => 5,
            'filter' => '',
            'order' => '+title'
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getScheduledEvents",
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

    public function testGetUnpublishedEventBySummit()
    {
        $params = [

            'id' => 23,
            //'filter' => ['speaker=@Jimmy', 'speaker=@Chimmy'],
            'filter' => ['speaker=@Jimmy,speaker=@Chimmy'],
            'expand' => 'speakers',
        ];

        $headers = [

            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getUnpublishedEvents",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $events = json_decode($content);
        $this->assertTrue(!is_null($events));
    }
}