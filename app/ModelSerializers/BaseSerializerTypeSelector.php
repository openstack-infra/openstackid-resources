<?php namespace ModelSerializers;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Models\Foundation\Main\IGroup;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\main\Group;
/**
 * Class BaseSerializerTypeSelector
 * @package ModelSerializers
 */
final class BaseSerializerTypeSelector implements ISerializerTypeSelector
{

    /**
     * @var IResourceServerContext
     */
    private $resource_server_context;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * BaseSerializerTypeSelector constructor.
     * @param IMemberRepository $member_repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        IMemberRepository $member_repository,
        IResourceServerContext $resource_server_context
    )
    {
        $this->resource_server_context = $resource_server_context;
        $this->member_repository       = $member_repository;
    }

    /**
     * @return string
     */
    public function getSerializerType()
    {
        $serializer_type = SerializerRegistry::SerializerType_Public;
        $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
        if(!is_null($current_member_id) && $member = $this->member_repository->getById($current_member_id)){
            if($member->isOnGroup(IGroup::SummitAdministrators)){
                $serializer_type = SerializerRegistry::SerializerType_Private;
            }
        }
        return $serializer_type;
    }
}