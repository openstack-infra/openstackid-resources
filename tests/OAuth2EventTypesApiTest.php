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
        return $event_types;
    }

    public function testGetEventTypesByClassNameCSV(){
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
            "OAuth2SummitsEventTypesApiController@getAllBySummitCSV",
            $params,
            [],
            [],
            [],
            $headers
        );

        $csv = $response->getContent();
        $this->assertResponseStatus(200);
        $this->assertTrue(!empty($csv));
    }

    public function testGetEventTypesDefaultOnes(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'is_default==1',
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
        return $event_types;
    }

    public function testGetEventTypesNonDefaultOnes(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'is_default==0',
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
        return $event_types;
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

    public function testAddEventType($summit_id = 23){
        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_eventtype';
        $data = [
            'name'       => $name,
            'class_name' => \models\summit\SummitEventType::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitsEventTypesApiController@addEventTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $event_type = json_decode($content);
        $this->assertTrue(!is_null($event_type));
        return $event_type;
    }

    public function testUpdateEventType($summit_id = 23){

        $new_event_type = $this->testAddEventType($summit_id);

        $params = [
            'id'            => $summit_id,
            'event_type_id' => $new_event_type->id,
        ];

        $data = [
            'color'       => "FFAAFF",
            'class_name' => \models\summit\SummitEventType::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitsEventTypesApiController@updateEventTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $event_type = json_decode($content);
        $this->assertTrue(!is_null($event_type));
        $this->assertTrue($event_type->color == '#FFAAFF');
        return $event_type;
    }

    public function testDeleteDefaultOne($summit_id = 23){

        $event_types = $this->testGetEventTypesDefaultOnes($summit_id);

        $params = [
            'id'            => $summit_id,
            'event_type_id' => $event_types->data[0]->id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitsEventTypesApiController@deleteEventTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(!empty($content));
        $this->assertResponseStatus(412);
    }

    public function testDeleteNonDefaultOne($summit_id = 23){

        $event_types = $this->testGetEventTypesNonDefaultOnes($summit_id);

        $params = [
            'id'            => $summit_id,
            'event_type_id' => $event_types->data[0]->id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitsEventTypesApiController@deleteEventTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(empty($content));
        $this->assertResponseStatus(204);
    }

}