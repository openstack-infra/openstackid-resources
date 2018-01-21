<?php
/*
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
 * Class OAuth2PromoCodesApiTest
 */
final class OAuth2PromoCodesApiTest extends ProtectedApiTest
{
    public function testGetPromoCodesDiscount(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'code=@DISCOUNT_',
            'order'    => '+code'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $promo_codes = json_decode($content);
        $this->assertTrue(!is_null($promo_codes));
    }

    public function testGetPromoCodesByClassNameSpeakerSummitRegistrationPromoCode(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => 'class_name=='.\models\summit\SpeakerSummitRegistrationPromoCode::ClassName,
            'order'    => '+code'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $promo_codes = json_decode($content);
        $this->assertTrue(!is_null($promo_codes));
    }

    public function testGetPromoCodesByClassNameOR(){
        $params = [

            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => [
                'class_name=='.\models\summit\SpeakerSummitRegistrationPromoCode::ClassName.','. 'class_name=='.\models\summit\MemberSummitRegistrationPromoCode::ClassName,
            ],
            'order'    => '+code'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $promo_codes = json_decode($content);
        $this->assertTrue(!is_null($promo_codes));
    }

    public function testGetPromoCodesByClassNameORInvalidClassName(){
        $params = [
            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => [
                'class_name=='.\models\summit\SpeakerSummitRegistrationPromoCode::ClassName.','. 'class_name==invalid'
            ],
            'order'    => '+code'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }

    public function testGetPromoCodesFilterByEmailOwner(){
        $params = [
            'id'       => 23,
            'page'     => 1,
            'per_page' => 10,
            'filter'   => [
                'speaker_email=@muroi',
            ],
            'order'    => '+code',
            'expand'   => 'speaker,creator'
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $promo_codes = json_decode($content);
        $this->assertTrue(!is_null($promo_codes));
    }
}