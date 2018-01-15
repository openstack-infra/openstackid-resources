<?php namespace App\Http\Middleware;
/**
 * Copyright 2016 OpenStack Foundation
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
use Illuminate\Support\Facades\Response;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;

/**
 * Class UserAuthEndpoint
 * @package App\Http\Middleware
 */
final class UserAuthEndpoint
{

    /**
     * @var IResourceServerContext
     */
    private $context;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    public function __construct
    (
        IResourceServerContext $context,
        IMemberRepository $member_repository
    )
    {
        $this->context           = $context;
        $this->member_repository = $member_repository;
    }

    public function handle($request, Closure $next, $required_groups)
    {
        $member_id = $this->context->getCurrentUserExternalId();
        if (is_null($member_id)) return $next($request);

        $member = $this->member_repository->getById($member_id);

        if (is_null($member)){
            $http_response = Response::json(['error' => 'member not found'], 403);
            return $http_response;
        }
        $required_groups = explode('|', $required_groups);

        foreach ($required_groups as $required_group) {
            if($member->isOnGroup($required_group))
                return $next($request);
        }

        $http_response = Response::json(['error' => 'unauthorized member'], 403);
        return $http_response;
    }
}