<?php

/**
 * Copyright 2016 OpenStack Foundation
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
final class OAuth2MembersApiTest extends ProtectedApiTest
{

    public function testGetMembers()
    {

        $params = [
            //AND FILTER
            'filter' => ['first_name=@Seba', 'last_name=@Marcet'],
            'order'  => '+first_name,-last_name'
        ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2MembersApiController@getMembers",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $members = json_decode($content);
        $this->assertTrue(!is_null($members));
        $this->assertResponseStatus(200);
    }

    public function testGetMembersByEmail()
    {
        $params = [
            'filter' => 'email=@sebastian@tipit.net',
            'order'  => '+first_name,-last_name',
            'expand' => 'groups'
        ];

        $headers  = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2MembersApiController@getMembers",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $members = json_decode($content);
        $this->assertTrue(!is_null($members));
        $this->assertResponseStatus(200);
    }

    public function testGetMembersByEmail2()
    {
        $params = [
            'filter' => ['email==sean.mcginnis@gmail.com', "email_verified==0"],
        ];

        $headers  = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2MembersApiController@getMembers",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $members = json_decode($content);
        $this->assertTrue(!is_null($members));
        $this->assertResponseStatus(200);
    }
}