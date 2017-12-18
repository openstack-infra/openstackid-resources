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

class OAuth2SummitEventsBulkActionsTest extends ProtectedApiTest
{
    public function testUpdateEvents()
    {
        $params = [
            'id' => 23,
        ];

        $data = [

           'events' => [
               [
                   'id' => 20420,
                   'title' => 'Making OpenContrail an ubiquitous SDN for OpenStack and Kubernetes!'
               ],
               [
                   'id' => 20421,
                   'title' => 'OpenContrail - from A to B, front to back, top to bottom, past to present, soup to nuts!'
               ]
           ]
        ];


        $headers = [

            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "PUT",
            "OAuth2SummitEventsApiController@updateEvents",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(204);
        $content = $response->getContent();
    }

    public function testGetEventByIdOR()
    {
        $params = [
            'id' => 23,
            'filter' => [

                'id==20420,id==20421,id==20427,id==20428',
            ]
        ];

        $headers = [

            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitEventsApiController@getEvents",
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