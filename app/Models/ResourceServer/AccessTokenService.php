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
use Illuminate\Support\Facades\Log;
use libs\utils\ConfigurationException;

/**
 * Class AccessTokenService
 * @package models\resource_server
 */
class AccessTokenService implements IAccessTokenService {

    /**
     * @param string $token_value
     * @return AccessToken
     * @throws \Exception
     */
    public function get($token_value)
    {
        $token = null;

        try{

        $client        = new Client([
            'defaults' => [
                'timeout'         => Config::get('curl.timeout',60),
                'allow_redirects' => Config::get('curl.allow_redirects',false),
                'verify'          => Config::get('curl.verify_ssl_cert',true)
            ]
        ]);

        $client_id       = Config::get('app.openstackid_client_id','');
        $client_secret   = Config::get('app.openstackid_client_secret','');
        $auth_server_url = Config::get('app.openstackid_base_url','');

        if(empty($client_id)) {
            throw new ConfigurationException('app.openstackid_client_id param is missing!');
        }

        if(empty($client_secret)) {
            throw new ConfigurationException('app.openstackid_client_secret param is missing!');
        }

        if(empty($auth_server_url)) {
                throw new ConfigurationException('app.openstackid_base_url param is missing!');
        }

        $response      = $client->post($auth_server_url.'/oauth2/token/introspection',
            [
                'query'   => ['token' => $token_value],
                'headers' => ['Authorization' => " Basic " . base64_encode($client_id . ':' . $client_secret)]
            ]);

        $code = (int)$response->getStatusCode();

        if($code !== 200){
            throw new OAuth2InvalidIntrospectionResponse(sprintf('http code %s', $code));
        }

        $token_info = $response->json();

        $token = AccessToken::createFromParams( $token_info['access_token'],
            $token_info['scope'],
            $token_info['client_id'],
            $token_info['audience'],
            $token_info['user_id'],
            $token_info['expires_in'],
            $token_info['client_type'],
            isset($token_info['allowed_return_uris']) ? $token_info['allowed_return_uris'] : null,
            isset($token_info['allowed_origins']) ? $token_info['allowed_origins'] : null);
        }
        catch(\Exception $ex){
            Log::error($ex);
            throw $ex;
        }
        return $token;
    }
}