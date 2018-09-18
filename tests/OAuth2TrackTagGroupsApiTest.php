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

final class OAuth2TrackTagGroupsApiTest extends ProtectedApiTest
{
    public function testGetTrackTagGroups($summit_id = 25)
    {

        $params = [
            'id' => $summit_id,
            'expand' => 'allowed_tags,tag',
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2SummitTrackTagGroupsApiController@getTrackTagGroupsBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $groups = json_decode($content);
        $this->assertTrue(!is_null($groups));
        $this->assertResponseStatus(200);
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddTrackTagGroup($summit_id = 25){
        $params = [
            'id' => $summit_id,
            'expand' => 'allowed_tags,tag'
        ];

        $name       = str_random(16).'_track_tag_group_name';
        $label      = str_random(16).'_track_tag_group_label';
        $data = [
            'name'           => $name,
            'label'         => $label,
            'is_mandatory'  => false,
            'allowed_tags' => ['101','Case Study', 'Demo'],
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitTrackTagGroupsApiController@addTrackTagGroup",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $track_tag_group = json_decode($content);
        $this->assertTrue(!is_null($track_tag_group));
        return $track_tag_group;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testUpdateTrackTagGroup($summit_id = 25){
        $new_track_tag_group = $this->testAddTrackTagGroup($summit_id);
        $params = [
            'id' => $summit_id,
            'track_tag_group_id' => $new_track_tag_group->id,
            'expand' => 'allowed_tags,tag'
        ];

        $name       = str_random(16).'_track_tag_group_name_update';
        $label      = str_random(16).'_track_tag_group_label_update';
        $data = [
            'name'           => $name,
            'label'         => $label,
            'order'  => 1,
            'allowed_tags' => ['101','Case Study', 'Demo', 'Demo2'],
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitTrackTagGroupsApiController@updateTrackTagGroup",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $track_tag_group = json_decode($content);
        $this->assertTrue(!is_null($track_tag_group));
        $this->assertTrue($track_tag_group->order == 1);
        return $track_tag_group;
    }

    public function testDeleteTrackTagGroup($summit_id = 25){
        $new_track_tag_group = $this->testAddTrackTagGroup($summit_id);
        $params = [
            'id' => $summit_id,
            'track_tag_group_id' => $new_track_tag_group->id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitTrackTagGroupsApiController@deleteTrackTagGroup",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetTrackTagGroupById($summit_id = 25){
        $new_track_tag_group = $this->testAddTrackTagGroup($summit_id);
        $params = [
            'id' => $summit_id,
            'track_tag_group_id' => $new_track_tag_group->id,
            'expand' => 'allowed_tags,tag'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitTrackTagGroupsApiController@getTrackTagGroup",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
    }

    public function testGetTags($summit_id = 25)
    {

        $params = [
            'id' => $summit_id,
            //AND FILTER
            'filter' => ['tag=@101'],
            'order'  => '+id',
            'expand' => 'tag,track_tag_group'
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2SummitTrackTagGroupsApiController@getAllowedTags",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $tags = json_decode($content);
        $this->assertTrue(!is_null($tags));
        $this->assertResponseStatus(200);
    }

    public function testSeedDefaultTrackTagGroups($summit_id = 26)
    {

        $params = [
            'id' => $summit_id,
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "POST",
            "OAuth2SummitTrackTagGroupsApiController@seedDefaultTrackTagGroups",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $tags = json_decode($content);
        $this->assertTrue(!is_null($tags));
        $this->assertResponseStatus(201);
    }

    /**
     * @param int $summit_id
     * @param int $tag_id
     */
    public function testSeedTagOnAllTracks($summit_id = 25, $tag_id = 4590)
    {

        $params = [
            'id' => $summit_id,
            'tag_id' => $tag_id,
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "POST",
            "OAuth2SummitTrackTagGroupsApiController@seedTagOnAllTracks",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $response = json_decode($content);
        $this->assertResponseStatus(201);
    }

    /**
     * @param int $summit_id
     * @param int $track_tag_group_id
     * @param $track_id
     */
    public function testSeedTagTrackGroupOnTrack($summit_id = 25, $track_tag_group_id = 6, $track_id = 268)
    {

        $params = [
            'id' => $summit_id,
            'track_tag_group_id' => $track_tag_group_id,
            'track_id' => $track_id,
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "POST",
            "OAuth2SummitTrackTagGroupsApiController@seedTagTrackGroupOnTrack",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $response = json_decode($content);
        $this->assertResponseStatus(201);
    }

}