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
use App\Models\ResourceServer\IApiEndpointRepository;
use App\Models\ResourceServer\IEndpointRateLimitByIPRepository;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Routing\Middleware\ThrottleRequests;
use libs\utils\RequestUtils;

/**
 * Class RateLimitMiddleware
 * @package App\Http\Middleware
 */
final class RateLimitMiddleware extends ThrottleRequests
{

    /**
     * @var IApiEndpointRepository
     */
    private $endpoint_repository;

    /**
     * @var IEndpointRateLimitByIPRepository
     */
    private $endpoint_rate_limit_by_ip_repository;

    /**
     * RateLimitMiddleware constructor.
     * @param IApiEndpointRepository $endpoint_repository
     * @param IEndpointRateLimitByIPRepository $endpoint_rate_limit_by_ip_repository
     * @param RateLimiter $limiter
     */
    public function __construct
    (
        IApiEndpointRepository $endpoint_repository,
        IEndpointRateLimitByIPRepository $endpoint_rate_limit_by_ip_repository,
        RateLimiter $limiter
    )
    {
        parent::__construct($limiter);
        $this->endpoint_repository                  = $endpoint_repository;
        $this->endpoint_rate_limit_by_ip_repository = $endpoint_rate_limit_by_ip_repository;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param int $max_attempts
     * @param int $decay_minutes
     * @return \Illuminate\Http\Response|mixed
     */
    public function handle($request, Closure $next, $max_attempts = 0, $decay_minutes = 0)
    {
        $route     = RequestUtils::getCurrentRoutePath($request);
        $method    = $request->getMethod();
        $endpoint  = $this->endpoint_repository->getApiEndpointByUrlAndMethod($route, $method);
        $key       = $this->resolveRequestSignature($request);
        $client_ip = $request->getClientIp();

        if (!is_null($endpoint) && $endpoint->getRateLimit() > 0) {
            $max_attempts = $endpoint->getRateLimit();
        }

        if (!is_null($endpoint) && $endpoint->getRateLimitDecay() > 0) {
            $decay_minutes = $endpoint->getRateLimitDecay();
        }

        $endpoint_rate_limit_by_ip = $this->endpoint_rate_limit_by_ip_repository->getByIPRouteMethod
        (
            $client_ip,
            $route,
            $method
        );

        if(!is_null($endpoint_rate_limit_by_ip)){
            $max_attempts  = $endpoint_rate_limit_by_ip->getRateLimit();
            $decay_minutes = $endpoint_rate_limit_by_ip->getRateLimitDecay();
        }

        if ($max_attempts == 0 || $decay_minutes == 0) {
            // short circuit (infinite)
            return $next($request);
        }

        if ($this->limiter->tooManyAttempts($key, $max_attempts, $decay_minutes)) {
            return $this->buildResponse($key, $max_attempts);
        }

        $this->limiter->hit($key, $decay_minutes);

        $response = $next($request);

        return $this->addHeaders(
            $response, $max_attempts,
            $this->calculateRemainingAttempts($key, $max_attempts)
        );
    }
}