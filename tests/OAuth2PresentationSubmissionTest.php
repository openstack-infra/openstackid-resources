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

class OAuth2PresentationSubmissionTest extends ProtectedApiTest
{
    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testSubmitPresentation($summit_id = 26){
        $params = [
            'id' => $summit_id,
        ];

        $title       = str_random(16).'_presentation';
        $data = [
            'title'       => $title,
            'description' => 'this is a description',
            'social_description'  => 'this is a social description',
            'level'  => 'N/A',
            'attendees_expected_learnt'  => 'super duper',
            'type_id'  => 184,
            'track_id'  => 294,
            'attending_media' => true,
            'links' => ['https://www.google.com'],
            'tags' => ['Upstream Development']
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2PresentationApiController@submitPresentation",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $presentation = json_decode($content);
        $this->assertTrue(!is_null($presentation));
        $this->assertEquals($title, $presentation->title);
        return $presentation;
    }


    public function testAddSpeaker2Presentation($summit_id = 26){
        $new_presentation = $this->testSubmitPresentation($summit_id);
        $params = [
            'id' => $summit_id,
            'presentation_id' => $new_presentation->id,
            'speaker_id' => 1
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitSpeakersApiController@addSpeakerToMyPresentation",
            $params,
            [],
            [],
            [],
            $headers,
            ''
        );

    }
    /**
     * @param int $summit_id
     */
    public function testDeletePresentation($summit_id = 26){
        $new_presentation = $this->testSubmitPresentation($summit_id);
        $params = [
            'id' => $summit_id,
            'presentation_id' => $new_presentation->id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2PresentationApiController@deletePresentation",
            $params,
            [],
            [],
            [],
            $headers,
            ''
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }
}