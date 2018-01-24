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

    public function testGetPromoCodesMetadata(){
        $params = [
            'id'       => 23,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getMetadata",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $metadata = json_decode($content);
        $this->assertTrue(!is_null($metadata));
    }

    public function testAddPromoCode($summit_id = 23, $code = "12344KG_SPEAKER"){
        $params = [
            'id' => $summit_id,
        ];

        $data = [
            'code'       => $code,
            'class_name' => \models\summit\MemberSummitRegistrationPromoCode::ClassName,
            'first_name' => 'Sebastian',
            'last_name'  => 'Marcet',
            'email'      => 'test@test.com',
            'type'       => \models\summit\MemberSummitRegistrationPromoCode::$valid_type_values[0]
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitPromoCodesApiController@addPromoCodeBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $promo_code = json_decode($content);
        $this->assertTrue(!is_null($promo_code));
        return $promo_code;
    }

    public function testUpdatePromoCode($summit_id  = 23){

        $code       = str_random(16).'_PROMOCODE_TEST';
        $promo_code = $this->testAddPromoCode($summit_id, $code);
        $params = [
            'id'            => $summit_id,
            'promo_code_id' => $promo_code->id
        ];

        $data = [
            'code'       => $code.'_UPDATE',
            'class_name' => \models\summit\MemberSummitRegistrationPromoCode::ClassName,
            'first_name' => 'Sebastian update',
            'last_name'  => 'Marcet update',
            'email'      => 'test@test.com',
            'type'       => \models\summit\MemberSummitRegistrationPromoCode::$valid_type_values[2]
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitPromoCodesApiController@updatePromoCodeBySummit",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $promo_code = json_decode($content);
        $this->assertTrue(!is_null($promo_code));
        return $promo_code;
    }

    public function testDeletePromoCode($summit_id  = 23){

        $code       = str_random(16).'_PROMOCODE_TEST';
        $promo_code = $this->testAddPromoCode($summit_id, $code);
        $params = [
            'id'            => $summit_id,
            'promo_code_id' => $promo_code->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitPromoCodesApiController@deletePromoCodeBySummit",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testGetPromoCodeById($summit_id  = 23){

        $code       = str_random(16).'_PROMOCODE_TEST';
        $promo_code = $this->testAddPromoCode($summit_id, $code);
        $params = [
            'id'            => $summit_id,
            'promo_code_id' => $promo_code->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitPromoCodesApiController@getPromoCodeBySummit",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $promo_code = json_decode($content);
        $this->assertTrue(!is_null($promo_code));
        return $promo_code;
    }

    public function testEmailPromoCode($summit_id  = 23){

        $code       = str_random(16).'_PROMOCODE_TEST';
        $promo_code = $this->testAddPromoCode($summit_id, $code);
        $params = [
            'id'            => $summit_id,
            'promo_code_id' => $promo_code->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitPromoCodesApiController@sendPromoCodeMail",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
    }

    public function testEmailPromoCodeSendTwice($summit_id  = 23){

        $code       = str_random(16).'_PROMOCODE_TEST';
        $promo_code = $this->testAddPromoCode($summit_id, $code);
        $params = [
            'id'            => $summit_id,
            'promo_code_id' => $promo_code->id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitPromoCodesApiController@sendPromoCodeMail",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $response = $this->action(
            "POST",
            "OAuth2SummitPromoCodesApiController@sendPromoCodeMail",
            $params,
            [],
            [],
            [],
            $headers

        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }
}