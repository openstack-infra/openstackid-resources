<?php namespace models\resource_server;

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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use libs\oauth2\InvalidGrantTypeException;
use libs\oauth2\OAuth2InvalidIntrospectionResponse;
use libs\oauth2\OAuth2Protocol;
use libs\utils\ConfigurationException;
use libs\utils\ICacheService;
use models\oauth2\AccessToken;
use Log;

/**
 * Class AccessTokenService
 * @package models\resource_server
 */
final class AccessTokenService implements IAccessTokenService
{

    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * @param ICacheService $cache_service
     */
    public function __construct(ICacheService $cache_service)
    {
        $this->cache_service = $cache_service;
    }

    /**
     * @param string $token_value
     * @return AccessToken
     * @throws \Exception
     */
    public function get($token_value)
    {
        $token          = null;
        $cache_lifetime = intval(Config::get('server.access_token_cache_lifetime', 300));

        $token_info     = $this->cache_service->getHash(md5($token_value), array
        (
            'access_token',
            'scope',
            'client_id',
            'audience',
            'user_id',
            'user_external_id',
            'expires_in',
            'application_type',
            'allowed_return_uris',
            'allowed_origins'
        ));

        if (count($token_info) === 0)
        {
            Log::debug("getting token from remote call ...");
            $token_info = $this->makeRemoteCall($token_value);
            $this->cache_service->storeHash(md5($token_value), $token_info, $cache_lifetime );
        }
        else
        {
            $cache_remaining_lifetime = intval($this->cache_service->ttl(md5($token_value)));
            $token_info['expires_in'] = intval($token_info['expires_in']) - ( $cache_lifetime - $cache_remaining_lifetime);
            Log::debug(sprintf("token life time %s - token cache remaining lifetime %s", $token_info['expires_in'], $cache_remaining_lifetime));
        }

        $token = AccessToken::createFromParams
        (
            $token_info['access_token'],
            $token_info['scope'],
            $token_info['client_id'],
            $token_info['audience'],
            isset($token_info['user_id'])? intval($token_info['user_id']):null,
            isset($token_info['user_external_id'])? intval($token_info['user_external_id']) : null,
            (int)$token_info['expires_in'],
            $token_info['application_type'],
            isset($token_info['allowed_return_uris']) ? $token_info['allowed_return_uris'] : null,
            isset($token_info['allowed_origins']) ? $token_info['allowed_origins'] : null
        );

        $str_token_info = "";
        foreach($token_info as $k => $v){
            $str_token_info  .= sprintf("-%s=%s-", $k, $v);
        }
        Log::debug("token info : ". $str_token_info);

        return $token;
    }

    /**
     * @param $token_value
     * @return mixed
     * @throws ConfigurationException
     * @throws InvalidGrantTypeException
     * @throws OAuth2InvalidIntrospectionResponse
     * @throws \Exception
     */
    private function makeRemoteCall($token_value)
    {

        try {
            $client = new Client([
                'defaults' => [
                    'timeout' => Config::get('curl.timeout', 60),
                    'allow_redirects' => Config::get('curl.allow_redirects', false),
                    'verify' => Config::get('curl.verify_ssl_cert', true)
                ]
            ]);

            $client_id = Config::get('app.openstackid_client_id', '');
            $client_secret = Config::get('app.openstackid_client_secret', '');
            $auth_server_url = Config::get('app.openstackid_base_url', '');

            if (empty($client_id)) {
                throw new ConfigurationException('app.openstackid_client_id param is missing!');
            }

            if (empty($client_secret)) {
                throw new ConfigurationException('app.openstackid_client_secret param is missing!');
            }

            if (empty($auth_server_url)) {
                throw new ConfigurationException('app.openstackid_base_url param is missing!');
            }

            $response = $client->post(
                $auth_server_url . '/oauth2/token/introspection',
                [
                    'query' => ['token' => $token_value],
                    'headers' => ['Authorization' => " Basic " . base64_encode($client_id . ':' . $client_secret)]
                ]
            );

            $content_type = $response->getHeader('content-type');
            if(!str_contains($content_type, 'application/json'))
            {
                // invalid content type
                throw new \Exception($response->getBody());
            }
            $token_info   = $response->json();

            return $token_info;

        } catch (RequestException $ex)
        {
            Log::error($ex->getMessage());
            $response     = $ex->getResponse();
            $content_type = $response->getHeader('content-type');
            $is_json      = str_contains($content_type, 'application/json');
            $body         = ($is_json) ? $response->json(): $response->getBody();

            $code = $response->getStatusCode();
            if ($code === 400 && $is_json && isset($body['error']) && $body['error'] === OAuth2Protocol::OAuth2Protocol_Error_InvalidToken)
            {
                throw new InvalidGrantTypeException($body['error']);
            }
            throw new OAuth2InvalidIntrospectionResponse(sprintf('http code %s', $ex->getCode()));
        }
    }
}