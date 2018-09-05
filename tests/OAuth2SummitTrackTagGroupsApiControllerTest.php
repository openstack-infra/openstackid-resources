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
 * Class OAuth2SummitTrackTagGroupsApiControllerTest
 */
final class OAuth2SummitTrackTagGroupsApiControllerTest extends ProtectedApiTest
{
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

}