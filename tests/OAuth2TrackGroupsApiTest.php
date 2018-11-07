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

final class OAuth2TrackGroupsApiTest extends ProtectedApiTest
{
    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetTrackGroups($summit_id = 23)
    {

        $params = [
           'id'      => $summit_id,
            'expand' => 'tracks',
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2PresentationCategoryGroupController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $track_groups = json_decode($content);
        $this->assertTrue(!is_null($track_groups));
        $this->assertResponseStatus(200);
        return $track_groups;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetTrackGroupsMetadata($summit_id = 23)
    {

        $params = [
            'id'      => $summit_id,
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2PresentationCategoryGroupController@getMetadata",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $metadata = json_decode($content);
        $this->assertTrue(!is_null($metadata));
        $this->assertResponseStatus(200);
        return $metadata;
    }


    /**
     * @param int $summit_id
     */
    public function testGetTrackGroupById($summit_id = 24){
        $track_groups_response = $this->testGetTrackGroups($summit_id);

        $track_groups = $track_groups_response->data;

        $track_group  = null;
        foreach($track_groups as $track_group_aux){
            if($track_group_aux->class_name == \models\summit\PrivatePresentationCategoryGroup::ClassName){
                $track_group = $track_group_aux;
                break;
            }
        }

        $params = [
            'id'             => $summit_id,
            'track_group_id' => $track_group->id,
            'expand'         => 'tracks,allowed_groups',
        ];

        $headers  = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2PresentationCategoryGroupController@getTrackGroupBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content     = $response->getContent();
        $track_group = json_decode($content);
        $this->assertTrue(!is_null($track_group));
        $this->assertResponseStatus(200);
    }

    /**
     * @param int $summit_id
     */
    public function testGetTrackGroupsPrivate($summit_id = 23)
    {

        $params = [
            'id'     => $summit_id,
            'expand' => 'tracks',
            'filter' => ['class_name=='.\models\summit\PrivatePresentationCategoryGroup::ClassName],
            'order'  => '+title',
        ];

        $headers = ["HTTP_Authorization" => " Bearer " . $this->access_token];
        $response = $this->action(
            "GET",
            "OAuth2PresentationCategoryGroupController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $track_groups = json_decode($content);
        $this->assertTrue(!is_null($track_groups));
        $this->assertResponseStatus(200);
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddTrackGroup($summit_id = 24){
        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_track_group';
        $data = [
            'name'        => $name,
            'description' => 'test desc track group',
            'class_name'  => \models\summit\PrivatePresentationCategoryGroup::ClassName
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2PresentationCategoryGroupController@addTrackGroupBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $track_group = json_decode($content);
        $this->assertTrue(!is_null($track_group));
        return $track_group;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testUpdateTrackGroup($summit_id = 24){

        $track_group = $this->testAddTrackGroup($summit_id);

        $params = [
            'id'             => $summit_id,
            'track_group_id' => $track_group->id
        ];

        $data = [
            'description' => 'test desc track group update',
            'class_name'  => \models\summit\PrivatePresentationCategoryGroup::ClassName
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2PresentationCategoryGroupController@updateTrackGroupBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $track_group = json_decode($content);
        $this->assertTrue(!is_null($track_group));
        $this->assertTrue($track_group->description == 'test desc track group update');
        return $track_group;
    }

    public function testAssociateTrack2TrackGroup412($summit_id = 24){

        $track_group = $this->testAddTrackGroup($summit_id);

        $params = [
            'id'             => $summit_id,
            'track_group_id' => $track_group->id,
            'track_id'       => 1
        ];


        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2PresentationCategoryGroupController@associateTrack2TrackGroup",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);

    }

    public function testAssociateTrack2TrackGroup($summit_id = 24){

        $track_group = $this->testAddTrackGroup($summit_id);

        $params = [
            'id'             => $summit_id,
            'track_group_id' => $track_group->id,
            'track_id'       => 211
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2PresentationCategoryGroupController@associateTrack2TrackGroup",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
    }

    /**
     * @param int $summit_id
     * @param int $track_group_id
     */
    public function testDeleteExistentTrackGroup($summit_id = 24, $track_group_id = 85){

        $params = [
            'id'             => $summit_id,
            'track_group_id' => $track_group_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2PresentationCategoryGroupController@deleteTrackGroupBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }
}