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
    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetTracksByTitle($summit_id = 23){

        $params = [

            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'title=@con',
            'order'    => '+code',
            'expand'   => 'track_groups,allowed_tags'
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

    /**
     * @param int $summit_id
     * @param int $track_id
     * @return mixed
     */
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

    /**
     * @param int $summit_id
     */
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

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddTrack($summit_id = 23){
        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_track';
        $data = [
            'title'       => $name,
            'description' => 'test desc',
            'code'        => '',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitTracksApiController@addTrackBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $track = json_decode($content);
        $this->assertTrue(!is_null($track));
        return $track;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testUpdateTrack($summit_id = 23){

        $new_track = $this->testAddTrack($summit_id);

        $params = [
            'id'       => $summit_id,
            'track_id' => $new_track->id
        ];

        $name       = str_random(16).'_track';
        $data = [
            'description' => 'test desc updated',
            'code'        => 'SMX' ,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitTracksApiController@updateTrackBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $track = json_decode($content);
        $this->assertTrue(!is_null($track));
        $this->assertTrue(!empty($track->description) && $track->description == 'test desc updated');
        $this->assertTrue(!empty($track->code) && $track->code == 'SMX');
        return $track;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testDeleteNewTrack($summit_id = 23){

        $new_track = $this->testAddTrack($summit_id);

        $params = [
            'id'       => $summit_id,
            'track_id' => $new_track->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitTracksApiController@deleteTrackBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testDeleteOldTrack($summit_id = 23){

        $params = [
            'id'       => $summit_id,
            'track_id' => 155
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitTracksApiController@deleteTrackBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }

    public function testCopyTracks($from_summit_id = 24, $to_summit_id = 25){

        $params = [
            'id'            => $from_summit_id,
            'to_summit_id' => $to_summit_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitTracksApiController@copyTracksToSummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $added_tracks = json_decode($content);
        $this->assertTrue(!is_null($added_tracks));
    }
}