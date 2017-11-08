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
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse;
use libs\utils\ICacheService;
use Illuminate\Support\Facades\Log;
use models\oauth2\IResourceServerContext;

/**
 * Class CacheMiddleware
 * @package App\Http\Middleware
 */
final class CacheMiddleware
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
     * @param $request
     * @param Closure $next
     * @param $cache_lifetime
     * @return JsonResponse
     */
    public function handle($request, Closure $next, $cache_lifetime)
    {
        Log::debug('cache middleware invoked ...');
        $cache_lifetime = intval($cache_lifetime);
        if ($request->getMethod() !== 'GET')
        {
            // short circuit
            return $next($request);
        }

        $key          = $request->getPathInfo();
        $query        = $request->getQueryString();
        $current_time = time();

        if(!empty($query))
        {
            $query = explode('&', $query);
            foreach($query as $q)
            {
                $q = explode('=',$q);
                if(strtolower($q[0]) === 'q'|| strtolower($q[0]) === 'access_token'|| strtolower($q[0]) === 'token_type' ) continue;
                $key .= ".".implode("=",$q);
            }
        }

        if (str_contains($request->getPathInfo(), '/me'))
        {
            $key .= ':' . $this->context->getCurrentUserExternalId();
        }

        $data         = $this->cache_service->getSingleValue($key);
        $time         = $this->cache_service->getSingleValue($key.".generated");

        if (empty($data) || empty($time))
        {
            $time = $current_time;
            Log::debug(sprintf("cache value not found for key %s , getting from api...", $key));
            // normal flow ...
            $response = $next($request);
            if ($response instanceof JsonResponse && $response->getStatusCode() === 200)
            {
                // and if its json, store it on cache ...
                $data = $response->getData(true);
                $this->cache_service->setSingleValue($key, gzdeflate(json_encode($data), 9), $cache_lifetime);
                $this->cache_service->setSingleValue($key.".generated", $time, $cache_lifetime);
            }
        }
        else
        {
            $ttl = $this->cache_service->ttl($key);
            // cache hit ...
            Log::debug(sprintf("cache hit for %s - ttl %s ...", $key, $ttl));
            $response = new JsonResponse(json_decode(gzinflate($data), true), 200, [
                    'content-type'       => 'application/json',
                ]
            );
        }

        $response->headers->set('xcontent-timestamp', $time);
        $response->headers->set('Cache-Control', sprintf('private, max-age=%s', $cache_lifetime));
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', $time + $cache_lifetime));

        return $response;
    }
}