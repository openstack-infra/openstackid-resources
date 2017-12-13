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
}