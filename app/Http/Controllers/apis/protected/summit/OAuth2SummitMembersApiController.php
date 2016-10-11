<?php namespace App\Http\Controllers;
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

use Illuminate\Support\Facades\Request;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;

/**
 * Class OAuth2SummitMembersApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitMembersApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * OAuth2SummitMembersApiController constructor.
     * @param IMemberRepository $member_repository
     * @param ISummitRepository $summit_repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        IMemberRepository $member_repository,
        ISummitRepository $summit_repository,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->summit_repository  = $summit_repository;
        $this->repository         = $member_repository;
    }


    public function getMyMember($summit_id){

        $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
        if (is_null($summit)) return $this->error404();

        $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
        if (is_null($current_member_id)) return $this->error403();

        $current_member = $this->repository->getById($current_member_id);
        if (is_null($current_member)) return $this->error404();

        return $this->ok
        (
            SerializerRegistry::getInstance()->getSerializer($current_member)->serialize
            (
                Request::input('expand', ''),
                [],
                [],
                ['summit' => $summit]
            )
        );
    }
}