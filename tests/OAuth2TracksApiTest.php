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
 * Class OAuth2TracksApiTest
 */
final class OAuth2TracksApiTest extends ProtectedApiTest
{
    public function testGetTracksByTitle($summit_id = 23){
        $params = [

            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'title=@con',
            'order'    => '+code',
            'expand'   => 'track_groups'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitTracksApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $tracks = json_decode($content);
        $this->assertTrue(!is_null($tracks));
        return $tracks;
    }

    public function testGetTracksById($summit_id = 23, $track_id = 152){
        $params = [

            'id'       => $summit_id,
            'track_id' => $track_id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitTracksApiController@getTrackBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $track = json_decode($content);
        $this->assertTrue(!is_null($track));
        return $track;
    }

    public function testGetTracksByTitleCSV($summit_id = 23){
        $params = [

            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'title=@con',
            'order'    => '+code'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitTracksApiController@getAllBySummitCSV",
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
}