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

/**
 * Class OAuth2EventTypesApiTest
 */
final class OAuth2EventTypesApiTest extends ProtectedApiTest
{
    public function testGetEventTypesByClassName(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'class_name==EVENT_TYPE',
            'order'    => '+name'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitsEventTypesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $event_types = json_decode($content);
        $this->assertTrue(!is_null($event_types));
    }

    public function testGetEventTypesByClassNamePresentationType(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'class_name==PRESENTATION_TYPE',
            'order'    => '+name'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitsEventTypesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $event_types = json_decode($content);
        $this->assertTrue(!is_null($event_types));
    }
}