<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace models\resource_server;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use libs\oauth2\OAuth2InvalidIntrospectionResponse;
use models\oauth2\AccessToken;
/**
 * Class AccessTokenService
 * @package models\resource_server
 */
class AccessTokenService implements IAccessTokenService {

    /**
     * @param string $token_value
     * @return AccessToken
     * @throws OAuth2InvalidIntrospectionResponse
     */
    public function get($token_value)
    {
        $client        = new Client([
            'defaults' => [
                'timeout'         => 10,
                'allow_redirects' => false,
                'verify'          => false
            ]
        ]);
        $client_id     = Config::get('app.openstackid_client_id');
        $client_secret = Config::get('app.openstackid_client_secret');

        $response      = $client->post(Config::get('app.openstackid_base_url').'/oauth2/token/introspection',
            [
                'query'   => ['token' => $token_value],
                'headers' => ['Authorization' => " Basic " . base64_encode($client_id . ':' . $client_secret)]
            ]);

        $code = (int)$response->getStatusCode();
        if($code !== 200){
            throw new OAuth2InvalidIntrospectionResponse(sprintf('http code %s', $code));
        }
        $data  = $response->json();

        $token = AccessToken::createFromParams( $data['access_token'],
            $data['scope'],
            $data['client_id'],
            $data['audience'],
            $data['user_id'],
            $data['expires_in'],
            $data['client_type'],
            isset($data['allowed_return_uris']) ? $data['allowed_return_uris'] : null,
            isset($data['allowed_origins']) ? $data['allowed_origins'] : null);
        return $token;
    }
}