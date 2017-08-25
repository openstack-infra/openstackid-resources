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
            "AppliancesApiController@getAppliances",
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
            "AppliancesApiController@getAppliances",
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
            'expand'   => 'company,reviews',
        ];

        $response = $this->action(
            "GET",
            "DistributionsApiController@getDistributions",
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
            'expand'   => 'company',
        ];

        $response = $this->action(
            "GET",
            "ConsultantsApiController@getConsultants",
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
}