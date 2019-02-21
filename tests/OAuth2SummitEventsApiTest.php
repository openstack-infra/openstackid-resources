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

final class OAuth2SummitEventsApiTest extends ProtectedApiTest
{
    public function testPostEvent($summit_id = 23, $location_id = 0, $type_id = 0, $track_id = 0, $start_date = 1477645200, $end_date = 1477647600)
    {
        $params = array
        (
            'id' => $summit_id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title'         => 'Neutron: tbd',
            'description'    => 'TBD',
            'allow_feedback' => true,
            'type_id'        => $type_id,
            'tags'           => ['Neutron'],
            'track_id'       => $track_id
        );

        if($start_date > 0){
            $data['start_date'] = $start_date;
        }

        if($end_date > 0){
            $data['end_date'] = $end_date;
        }

        if($location_id > 0){
            $data['location_id'] = $location_id;
        }

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

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $event = json_decode($content);
        $this->assertTrue($event->id > 0);
        return $event;
    }

    public function testPostEventRSVPTemplateUnExistent($summit_id = 23, $location_id = 0, $type_id = 124, $track_id = 208, $start_date = 1477645200, $end_date = 1477647600)
    {
        $params = array
        (
            'id' => $summit_id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title'            => 'Neutron: tbd',
            'description'      => 'TBD',
            'allow_feedback'   => true,
            'type_id'          => $type_id,
            'tags'             => ['Neutron'],
            'track_id'         => $track_id,
            'rsvp_template_id' => 1,
        );

        if($start_date > 0){
            $data['start_date'] = $start_date;
        }

        if($end_date > 0){
            $data['end_date'] = $end_date;
        }

        if($location_id > 0){
            $data['location_id'] = $location_id;
        }

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

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }

    public function testPostEventRSVPTemplate($summit_id = 23, $location_id = 0, $type_id = 124, $track_id = 208, $start_date = 0, $end_date = 0)
    {
        $params = array
        (
            'id' => $summit_id,
        );

        $headers = array
        (
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        );

        $data = array
        (
            'title'            => 'Neutron: tbd',
            'description'      => 'TBD',
            'allow_feedback'   => true,
            'type_id'          => $type_id,
            'tags'             => ['Neutron'],
            'track_id'         => $track_id,
            'rsvp_template_id' => 12,
        );

        if($start_date > 0){
            $data['start_date'] = $start_date;
        }

        if($end_date > 0){
            $data['end_date'] = $end_date;
        }

        if($location_id > 0){
            $data['location_id'] = $location_id;
        }

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

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $event = json_decode($content);
        $this->assertTrue($event->id > 0);
        $this->assertTrue(!$event->rsvp_external);
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
            'id' => 23,
            'event_id' => 19255,
        );

        $data = array
        (
            'title' => 'Using HTTPS to Secure OpenStack Services Update',
            'speakers' => [210, 9161, 202]
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

    public function testPublishEvent($start_date = 1509789600, $end_date = 1509791400)
    {
        $event = $this->testPostEvent($summit_id = 23, $location_id = 0, $type_id = 124, $track_id = 206, $start_date, $end_date);
        unset($event->tags);

        $params = array
        (
            'id'         => $summit_id,
            'event_id'   => $event->id,
            'start_date' => $start_date,
            'end_date'   => $end_date
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

    public function testUpdateEventOccupancy(){

        $params = array
        (
            'id' => 23,
            'event_id' => 20345,
        );

        $data = [
            'occupancy' => '25%'
        ];

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

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $event = json_decode($content);
        $this->assertTrue($event->id > 0);
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

    public function testDeleteEvent($summit_id = 23, $event_id = 0)
    {
        if($event_id == 0) {
            $event = $this->testPostEvent($summit_id, $location_id = 0 , 117, 151, 0 , 0);
            $event_id = $event->id;
        }

        $params = [

            'id'       => $summit_id,
            'event_id' => $event_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "DELETE",
            "OAuth2SummitEventsApiController@deleteEvent",
            $params,
            [],
            [],
            [],
            $headers
        );

        $this->assertResponseStatus(204);
        //return $event;
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

    public function testCurrentSummitEventsWithFilterCSV()
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
            "OAuth2SummitEventsApiController@getEventsCSV",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $csv = $response->getContent();
        $this->assertResponseStatus(200);


        $this->assertTrue(!empty($csv));
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


    public function testGetORSpeakers($summit_id=24)
    {
        $params = array
        (
            'id' => $summit_id,
            'filter' => [
                'speaker_id==13987,speaker_id==12765'
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
            "OAuth2SummitEventsApiController@getScheduledEvents",
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

    public function testGetScheduleEmptySpotsBySummit()
    {
        $summit_repository   = EntityManager::getRepository(\models\summit\Summit::class);
        $summit              = $summit_repository->getById(25);
        $summit_time_zone    = $summit->getTimeZone();
        $start_datetime      = new DateTime( "2018-11-10 07:00:00", $summit_time_zone);
        $end_datetime        = new DateTime("2018-11-10 22:00:00", $summit_time_zone);
        $start_datetime_unix = $start_datetime->getTimestamp();
        $end_datetime_unix   = $end_datetime->getTimestamp();

        $params = [

            'id' => 25,
            'filter' =>
                [
                    'location_id==391',
                    'start_date>='.$start_datetime_unix,
                    'end_date<='.$end_datetime_unix,
                    'gap>=30',
                ],
        ];

        $headers = [

            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getScheduleEmptySpots",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $gaps = json_decode($content);
        $this->assertTrue(!is_null($gaps));
    }

    public function testGetUnpublishedEventBySummit()
    {
        $params = [

            'id' => 23,
            'filter' =>
                [
                    'selection_status==lightning-alternate',
                    'event_type_id==117',
                    'title=@test,abstract=@test,social_summary=@test,tags=@test,speaker=@test'
                ],
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

    public function testGetFilteredEvents($summit_id = 25)
    {
        $params = array
        (
            'id' => $summit_id ,
            'expand' => 'speakers,type',
            'filter' => [

                'title=@kubernets',
                'abstract=@kubernets',
                'tags=@kubernets',
                'speaker=@kubernets',
                'speaker_email=@kubernets',
                'id==kubernets'
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

}