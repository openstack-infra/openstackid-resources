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

namespace App\Http\Middleware;

use Closure;
use Config;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Http\JsonResponse;
use libs\utils\ICacheService;
use Log;
use models\oauth2\IResourceServerContext;

/**
 * Class CacheMiddleware
 * @package App\Http\Middleware
 */
class CacheMiddleware implements Middleware
{
    /**
     * @var ICacheService
     */
    private $cache_service;

    /**
     * @var IResourceServerContext
     */
    private $context;

    public function __construct(IResourceServerContext $context, ICacheService $cache_service)
    {
        $this->context = $context;
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
        $key = $request->getRequestUri();

        if ($request->getMethod() !== 'GET')
        {
            // shortcircuit
            return $next($request);
        }

        $cache_lifetime = intval(Config::get('server.response_cache_lifetime', 10000));

        if (str_contains($key, '/me'))
        {
            $key .= ':' . $this->context->getCurrentUserExternalId();
        }

        $data = $this->cache_service->getSingleValue($key);

        if (empty($data))
        {
            // normal flow ...
            $response = $next($request);
            if ($response instanceof JsonResponse && $response->getStatusCode() === 200)
            {
                // and if its json, store it on cache ...
                $data = $response->getData(true);
                $this->cache_service->setSingleValue($key, json_encode($data), $cache_lifetime);
            }
        } else {
            // cache hit ...
            Log::debug(sprintf("cache hit for %s ...", $key));
            $response = new JsonResponse($data, 200, array('content-type' => 'application/json'));
        }

        return $response;
    }
}