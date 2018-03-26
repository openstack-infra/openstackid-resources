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
}