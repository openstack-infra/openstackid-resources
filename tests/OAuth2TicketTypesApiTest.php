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

final class OAuth2TicketTypesApiTest extends ProtectedApiTest
{
    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetTicketTypes($summit_id=24){
        $params = [

            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+name'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitsTicketTypesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $ticket_types = json_decode($content);
        $this->assertTrue(!is_null($ticket_types));
        return $ticket_types;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetTicketTypesById($summit_id=24){
        $ticket_types_response = $this->testGetTicketTypes($summit_id);

        $params = [
            'id' => $summit_id,
            'ticket_type_id' => $ticket_types_response->data[0]->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitsTicketTypesApiController@getTicketTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $ticket_type = json_decode($content);
        $this->assertTrue(!is_null($ticket_type));
        return $ticket_type;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddTicketType($summit_id = 24){
        $params = [
            'id' => $summit_id,
        ];

        $name        = str_random(16).'_ticket_type';
        $external_id = str_random(16).'_external_id';

        $data = [
            'name'        => $name,
            'external_id' => $external_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitsTicketTypesApiController@addTicketTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $ticket_type = json_decode($content);
        $this->assertTrue(!is_null($ticket_type));
        $this->assertTrue($ticket_type->name == $name);
        $this->assertTrue($ticket_type->external_id == $external_id);
        return $ticket_type;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testUpdateTicketType($summit_id = 24){

        $ticket_type = $this->testAddTicketType($summit_id);

        $params = [
            'id'             => $summit_id,
            'ticket_type_id' => $ticket_type->id
        ];

        $data = [
            'description' => 'test description',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitsTicketTypesApiController@updateTicketTypeBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $ticket_type = json_decode($content);
        $this->assertTrue(!is_null($ticket_type));
        $this->assertTrue($ticket_type->description == 'test description');
        return $ticket_type;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testSeedDefaultTicketTypes($summit_id = 24){
        $params = [
            'id' => $summit_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitsTicketTypesApiController@seedDefaultTicketTypesBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $ticket_types = json_decode($content);
        $this->assertTrue(!is_null($ticket_types));
        return $ticket_types;
    }
}