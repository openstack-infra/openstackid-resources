<?php
/**
 * Copyright 2017 OpenStack Foundation
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
use Tests\TestCase;
/**
 * Class MarketplaceApiTest
 */
final class MarketplaceApiTest extends TestCase
{

    public function testGetAppliancesAll(){

        $params = [
            'page'     => 1,
            'per_page' => 100,
            'expand'   => 'company,reviews',
        ];

        $response = $this->action(
            "GET",
            "AppliancesApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $appliances = json_decode($content);
        $this->assertTrue(!is_null($appliances));
        $this->assertResponseStatus(200);
    }

    public function testGetAppliancesFilter(){

        $params = [
            'filter' => 'company=@Breqwa',
            'order'  => '+name',
            'expand' => 'company,reviews'
        ];

        $response = $this->action(
            "GET",
            "AppliancesApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $appliances = json_decode($content);
        $this->assertTrue(!is_null($appliances));
        $this->assertResponseStatus(200);
    }

    public function testGetAllDistros(){

        $params = [
            'page'     => 1,
            'per_page' => 100,
            'expand'   => '',
        ];

        $response = $this->action(
            "GET",
            "DistributionsApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $distros    = json_decode($content);
        $this->assertTrue(!is_null($distros));
        $this->assertResponseStatus(200);
    }

    public function testGetAllConsultants(){
        $params = [
            'page'     => 1,
            'per_page' => 100,
            'expand'   => 'offices,clients,spoken_languages,configuration_management_expertise,expertise_areas,services_offered',
        ];

        $response = $this->action(
            "GET",
            "ConsultantsApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $consultants    = json_decode($content);
        $this->assertTrue(!is_null($consultants));
        $this->assertResponseStatus(200);
    }

    public function testGetAllPrivateClouds(){
        $params = [
            'page'     => 1,
            'per_page' => 100,
            'expand'   => '',
        ];

        $response = $this->action(
            "GET",
            "PrivateCloudsApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $clouds    = json_decode($content);
        $this->assertTrue(!is_null($clouds));
        $this->assertResponseStatus(200);
    }

    public function testGetAllPublicClouds(){
        $params = [
            'page'     => 1,
            'per_page' => 100,
            'expand'   => '',
        ];

        $response = $this->action(
            "GET",
            "PublicCloudsApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $clouds    = json_decode($content);
        $this->assertTrue(!is_null($clouds));
        $this->assertResponseStatus(200);
    }

    public function testGetAllRemoteClouds(){
        $params = [
            'page'     => 1,
            'per_page' => 100,
            'expand'   => '',
        ];

        $response = $this->action(
            "GET",
            "RemoteCloudsApiController@getAll",
            $params,
            [],
            [],
            [],
            []
        );

        $content    = $response->getContent();
        $clouds    = json_decode($content);
        $this->assertTrue(!is_null($clouds));
        $this->assertResponseStatus(200);
    }
}