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
 * Class OAuth2SpeakersAssistancesApiTest
 */
final class OAuth2SpeakersAssistancesApiTest extends ProtectedApiTest
{
    public function testGetAllBySummit($summit_id = 23){

        $params = [
            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'order'    => '+id',
            'expand'   => 'speaker,summit'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersAssistanceApiController@getBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $assistances = json_decode($content);
        $this->assertTrue(!is_null($assistances));
        return $assistances;
    }

    public function testGetBySummitAndId($summit_id = 23, $assistance_id = 3129){

        $params = [
            'id'            => $summit_id,
            'assistance_id' => $assistance_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersAssistanceApiController@getSpeakerSummitAssistanceBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $assistance = json_decode($content);
        $this->assertTrue(!is_null($assistance));
        return $assistance;
    }

    public function testGetAllBySummitAndConfirmed($summit_id = 23){

        $params = [

            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'is_confirmed==1',
            'order'    => '+id',
            'expand'   => 'speaker'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersAssistanceApiController@getBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $assistances = json_decode($content);
        $this->assertTrue(!is_null($assistances));
        return $assistances;
    }

    public function testGetAllBySummitAndNonConfirmed($summit_id = 23){

        $params = [

            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'is_confirmed==0',
            'order'    => '+id',
            'expand'   => 'speaker'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSpeakersAssistanceApiController@getBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $assistances = json_decode($content);
        $this->assertTrue(!is_null($assistances));
        return $assistances;
    }

    public function testDeleteSummitAssistance($summit_id  = 23, $assistance_id = 3561){

        if($assistance_id <= 0) {
            $assistances   = $this->testGetAllBySummitAndNonConfirmed($summit_id);
            $assistance_id = $assistances->data[0]->id;
        }

        $params = [
            'id'            => $summit_id,
            'assistance_id' => $assistance_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitSpeakersAssistanceApiController@deleteSpeakerSummitAssistance",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testAddSummitAssistance($summit_id = 23){
        $params = [
            'id' => $summit_id,
        ];

        $data = [
            'speaker_id'   => 15,
            'checked_in'   => false,
            'registered'   => true,
            'is_confirmed' => false,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitSpeakersAssistanceApiController@addSpeakerSummitAssistance",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $assistance = json_decode($content);
        $this->assertTrue(!is_null($assistance));

        return $assistance;
    }

    public function testUpdateSummitAssistance($summit_id = 23){

        $response = $this->testGetAllBySummitAndConfirmed($summit_id);

        $params = [
            'id'            => $summit_id,
            'assistance_id' => $response->data[0]->id
        ];

        $data = [
           'is_confirmed'  => true,
           'on_site_phone' => '+5491133943659'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitSpeakersAssistanceApiController@updateSpeakerSummitAssistance",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $assistance = json_decode($content);
        $this->assertTrue(!is_null($assistance));
        return $assistance;
    }

    public function testSendAnnouncementEmail($summit_id = 23, $assistance_id = 3541){

        $params = [
            'id'            => $summit_id,
            'assistance_id' => $assistance_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitSpeakersAssistanceApiController@sendSpeakerSummitAssistanceAnnouncementMail",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
    }

}