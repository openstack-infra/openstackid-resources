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

class OAuth2OrganizationsApiTest extends ProtectedApiTest
{

    public function testGetOrganizations()
    {

        $params = [
            //AND FILTER
            'filter' => ['name=@tip'],
            'order'  => '-id'
        ];

        $headers = array("HTTP_Authorization" => " Bearer " . $this->access_token);
        $response = $this->action(
            "GET",
            "OAuth2OrganizationsApiController@getAll",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $organizations = json_decode($content);
        $this->assertTrue(!is_null($organizations));
        $this->assertResponseStatus(200);
    }

    public function testAddOrganization(){

        $name = str_random(16).'_org_name';
        $data = [
            'name'  => $name,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2OrganizationsApiController@addOrganization",
            [],
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $org = json_decode($content);
        $this->assertTrue(!is_null($org));
        return $org;
    }

}