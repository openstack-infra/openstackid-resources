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
class OAuth2SpeakersApiTest extends ProtectedApiTest
{

    public function testPostSpeaker($summit_id = 23)
    {
        $params = [
            'id' => $summit_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $data = [

            'title'      => 'Developer!',
            'first_name' => 'Sebastian',
            'last_name'  => 'Marcet',
            'email'      => 'sebastian.ge4.marcet@gmail.com'
        ];

        $response = $this->action
        (
            "POST",
            "OAuth2SummitSpeakersApiController@addSpeaker",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $speaker = json_decode($content);
        $this->assertTrue($speaker->id > 0);
        return $speaker;
    }

    public function testPostSpeakerRegCode($summit_id = 23)
    {
        $params = [

            'id' => $summit_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $data = [
            'title'             => 'Developer!',
            'first_name'        => 'Sebastian',
            'last_name'         => 'Marcet',
            'email'             => 'sebastian.ge7.marcet@gmail.com',
            'registration_code' => 'SPEAKER_00001'
        ];

        $response = $this->action
        (
            "POST",
            "OAuth2SummitSpeakersApiController@addSpeaker",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $speaker = json_decode($content);
        $this->assertTrue($speaker->id > 0);
        return $speaker;
    }

    public function testPostSpeakerExistent($summit_id = 23)
    {
        $params = [

            'id' => $summit_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $data = [

            'title'             => 'Developer!',
            'first_name'        => 'Sebastian',
            'last_name'         => 'Marcet',
            'email'             => 'sebastian@tipit.net',
        ];

        $response = $this->action
        (
            "POST",
            "OAuth2SummitSpeakersApiController@addSpeaker",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $speaker = json_decode($content);
        $this->assertTrue($speaker->id > 0);
        return $speaker;
    }

    public function testUpdateSpeaker($summit_id = 23)
    {
        $params = [

            'id'         => $summit_id,
            'speaker_id' => 9161
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $data = [
            'title'             => 'Legend!!!',
        ];

        $response = $this->action
        (
            "PUT",
            "OAuth2SummitSpeakersApiController@updateSpeaker",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $this->assertResponseStatus(201);
        $content = $response->getContent();
        $speaker = json_decode($content);
        $this->assertTrue($speaker->id > 0);
        return $speaker;
    }

    public function testGetCurrentSummitSpeakersOrderByID()
    {
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+id'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSpeakers",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speakers = json_decode($content);
        $this->assertTrue(!is_null($speakers));
    }

    public function testGetCurrentSummitSpeakersOrderByEmail()
    {
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+email'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSpeakers",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speakers = json_decode($content);
        $this->assertTrue(!is_null($speakers));
    }

    public function testGetCurrentSummitSpeakersByIDMultiple()
    {
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'id==13869,id==19'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSpeakers",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speakers = json_decode($content);
        $this->assertTrue(!is_null($speakers));
    }

    public function testGetCurrentSummitSpeakersByID()
    {
        $params = [
            'id'          => 23,
            'speaker_id'  => 13869
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSummitSpeaker",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speaker = json_decode($content);
        $this->assertTrue(!is_null($speaker));
    }

    public function testGetSpeaker(){

        $params = [
            'speaker_id' => 1
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersApiController@getSpeaker",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $speaker = json_decode($content);
        $this->assertTrue(!is_null($speaker));
    }

    public function testMergeSpeakers(){

        $params = [
            'speaker_from_id' => 3643,
            'speaker_to_id'   => 1
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $data = [
            'title'                => 1,
            'bio'                  => 1,
            'first_name'           => 1,
            'last_name'            => 1,
            'irc'                  => 1,
            'twitter'              => 1,
            'pic'                  => 1,
            'registration_request' => 1,
            'member'               => 1,
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitSpeakersApiController@merge",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
        $speaker = json_decode($content);
        $this->assertTrue(!is_null($speaker));
    }

}