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
use Illuminate\Support\Facades\Response;
use libs\utils\ICacheService;
use libs\utils\RequestUtils;
use models\resource_server\IApiEndpointRepository;

/**
 * Class RateLimitMiddleware
 * @package App\Http\Middleware
 */
final class RateLimitMiddleware implements Middleware
{

    /**
     * @var IApiEndpointRepository
     */
    private $endpoint_repository;

    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * @param IApiEndpointRepository $endpoint_repository
     * @param ICacheService $cache_service
     */
    public function __construct(IApiEndpointRepository $endpoint_repository, ICacheService $cache_service)
    {
        $this->endpoint_repository = $endpoint_repository;
        $this->cache_service = $cache_service;
    }

    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        // if response was not changed then short circuit ...
        if ($response->getStatusCode() === 304) {
            return $response;
        }

        $url = $request->getRequestUri();

        try {
            $route = RequestUtils::getCurrentRoutePath($request);
            $method = $request->getMethod();
            $endpoint = $this->endpoint_repository->getApiEndpointByUrlAndMethod($route, $method);

            if (!is_null($endpoint->rate_limit) && ($requestsPerHour = (int)$endpoint->rate_limit) > 0) {
                //do rate limit checking
                $key = sprintf('rate.limit.%s_%s_%s', $url, $method, $request->getClientIp());
                // Add if doesn't exist
                // Remember for 1 hour
                $this->cache_service->addSingleValue($key, 0, 3600);
                // Add to count
                $count = $this->cache_service->incCounter($key);
                if ($count > $requestsPerHour) {
                    // Short-circuit response - we're ignoring
                    $response = Response::json(array(
                        'message' => "You have triggered an abuse detection mechanism and have been temporarily blocked.
                         Please retry your request again later."
                    ), 403);
                    $ttl = (int)$this->cache_service->ttl($key);
                    $response->headers->set('X-RateLimit-Reset', $ttl, false);
                }
                $response->headers->set('X-Ratelimit-Limit', $requestsPerHour, false);
                $remaining = $requestsPerHour - (int)$count;
                if ($remaining < 0) {
                    $remaining = 0;
                }
                $response->headers->set('X-Ratelimit-Remaining', $remaining, false);
            }
        } catch (Exception $ex) {
            Log::error($ex);
        }

        return $response;
    }
}