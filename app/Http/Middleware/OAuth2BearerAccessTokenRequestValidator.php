<?php namespace App\Http\Middleware;

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

use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use libs\oauth2\BearerAccessTokenAuthorizationHeaderParser;
use libs\oauth2\InvalidGrantTypeException;
use libs\oauth2\OAuth2Protocol;
use libs\oauth2\OAuth2ResourceServerException;
use libs\oauth2\OAuth2WWWAuthenticateErrorResponse;
use libs\utils\RequestUtils;
use models\oauth2\IResourceServerContext;
use models\resource_server\IAccessTokenService;
use models\resource_server\IApiEndpointRepository;
use URL\Normalizer;

/**
 * Class OAuth2BearerAccessTokenRequestValidator
 * http://tools.ietf.org/html/rfc6749#section-7
 * @package App\Http\Middleware
 */
class OAuth2BearerAccessTokenRequestValidator implements Middleware
{

    /**
     * @var IResourceServerContext
     */
    private $context;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var IApiEndpointRepository
     */
    private $endpoint_repository;

    /**
     * @var IAccessTokenService
     */
    private $token_service;

    /**
     * @param IResourceServerContext $context
     * @param IApiEndpointRepository $endpoint_repository
     * @param IAccessTokenService $token_service
     */
    public function __construct(
        IResourceServerContext $context,
        IApiEndpointRepository $endpoint_repository,
        IAccessTokenService $token_service
    ) {
        $this->context = $context;
        $this->headers = $this->getHeaders();
        $this->endpoint_repository = $endpoint_repository;
        $this->token_service = $token_service;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return OAuth2WWWAuthenticateErrorResponse
     */
    public function handle($request, Closure $next)
    {
        $url = $request->getRequestUri();
        $method = $request->getMethod();
        $realm = $request->getHost();

        try {
            $route = RequestUtils::getCurrentRoutePath($request);
            if (!$route) {
                throw new OAuth2ResourceServerException(
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    sprintf('API endpoint does not exits! (%s:%s)', $url, $method)
                );
            }
            // http://tools.ietf.org/id/draft-abarth-origin-03.html
            $origin = $request->headers->has('Origin') ? $request->headers->get('Origin') : null;
            if (!empty($origin)) {
                $nm     = new Normalizer($origin);
                $origin = $nm->normalize();
            }

            //check first http basic auth header
            $auth_header = isset($this->headers['authorization']) ? $this->headers['authorization'] : null;
            if (!is_null($auth_header) && !empty($auth_header)) {
                $access_token_value = BearerAccessTokenAuthorizationHeaderParser::getInstance()->parse($auth_header);
            } else {
                // http://tools.ietf.org/html/rfc6750#section-2- 2
                // if access token is not on authorization header check on POST/GET params
                $access_token_value = Input::get(OAuth2Protocol::OAuth2Protocol_AccessToken, '');
            }

            if (is_null($access_token_value) || empty($access_token_value)) {
                //if access token value is not set, then error
                throw new OAuth2ResourceServerException(
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    'missing access token'
                );
            }

            $endpoint = $this->endpoint_repository->getApiEndpointByUrlAndMethod($route, $method);

            //api endpoint must be registered on db and active
            if (is_null($endpoint) || !$endpoint->isActive()) {
                throw new OAuth2ResourceServerException(
                    400,
                    OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                    sprintf('API endpoint does not exits! (%s:%s)', $route, $method)
                );
            }

            $token_info = $this->token_service->get($access_token_value);
            if(!is_null($token_info))
                Log::debug(sprintf("token lifetime %s", $token_info->getLifetime()));
            //check lifetime
            if (is_null($token_info) || $token_info->getLifetime() <= 0) {
                throw new InvalidGrantTypeException(OAuth2Protocol::OAuth2Protocol_Error_InvalidToken);
            }
            //check token audience
            $audience = explode(' ', $token_info->getAudience());
            if ((!in_array($realm, $audience))) {
                throw new InvalidGrantTypeException(OAuth2Protocol::OAuth2Protocol_Error_InvalidToken);
            }
            if ($token_info->getApplicationType() === 'JS_CLIENT' && str_contains($token_info->getAllowedOrigins(),
                    $origin) === false
            ) {
                //check origins
                throw new OAuth2ResourceServerException(
                    403,
                    OAuth2Protocol::OAuth2Protocol_Error_UnauthorizedClient,
                    'invalid origin'
                );
            }
            //check scopes
            $endpoint_scopes = explode(' ', $endpoint->getScope());
            $token_scopes    = explode(' ', $token_info->getScope());

            //check token available scopes vs. endpoint scopes
            if (count(array_intersect($endpoint_scopes, $token_scopes)) == 0) {
                Log::error(
                    sprintf(
                        'access token scopes (%s) does not allow to access to api url %s , needed scopes %s',
                        $token_info->getScope(),
                        $url,
                        implode(' OR ', $endpoint_scopes)
                    )
                );

                throw new OAuth2ResourceServerException(
                    403,
                    OAuth2Protocol::OAuth2Protocol_Error_InsufficientScope,
                    'the request requires higher privileges than provided by the access token',
                    implode(' ', $endpoint_scopes)
                );
            }

            //set context for api and continue processing
            $context = array
            (
                'access_token'     => $access_token_value,
                'expires_in'       => $token_info->getLifetime(),
                'client_id'        => $token_info->getClientId(),
                'scope'            => $token_info->getScope(),
                'application_type' => $token_info->getApplicationType()
            );

            if (!is_null($token_info->getUserId()))
            {
                $context['user_id']          = $token_info->getUserId();
                $context['user_external_id'] = $token_info->getUserExternalId();
            }

            $this->context->setAuthorizationContext($context);

        }
        catch (OAuth2ResourceServerException $ex1)
        {
            Log::error($ex1);
            $response = new OAuth2WWWAuthenticateErrorResponse(
                $realm,
                $ex1->getError(),
                $ex1->getErrorDescription(),
                $ex1->getScope(),
                $ex1->getHttpCode()
            );
            $http_response = Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate', $response->getWWWAuthenticateHeaderValue());

            return $http_response;
        }
        catch (InvalidGrantTypeException $ex2)
        {
            Log::error($ex2);
            $response = new OAuth2WWWAuthenticateErrorResponse(
                $realm,
                OAuth2Protocol::OAuth2Protocol_Error_InvalidToken,
                'the access token provided is expired, revoked, malformed, or invalid for other reasons.',
                null,
                401
            );
            $http_response = Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate', $response->getWWWAuthenticateHeaderValue());

            return $http_response;
        } catch (\Exception $ex) {
            Log::error($ex);
            $response = new OAuth2WWWAuthenticateErrorResponse(
                $realm,
                OAuth2Protocol::OAuth2Protocol_Error_InvalidRequest,
                'invalid request',
                null,
                400
            );
            $http_response = Response::json($response->getContent(), $response->getHttpCode());
            $http_response->header('WWW-Authenticate', $response->getWWWAuthenticateHeaderValue());

            return $http_response;
        }
        $response = $next($request);

        return $response;
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = array();
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                $headers[strtolower($name)] = $value;
            }
        } else {
            // @codeCoverageIgnoreEnd
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[strtolower($name)] = $value;
                }
            }
            foreach (Request::header() as $name => $value) {
                if (!array_key_exists($name, $headers)) {
                    $headers[strtolower($name)] = $value[0];
                }
            }
        }

        return $headers;
    }
}