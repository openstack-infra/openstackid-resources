<?php namespace App\Repositories;

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

use Doctrine\ORM\QueryBuilder;
use Illuminate\Support\Facades\App;
/**
 * Class SilverStripeDoctrineRepository
 * @package App\Repositories
 */
abstract class SilverStripeDoctrineRepository extends DoctrineRepository
{
    protected static $em_name = 'ss';

    /**
     * @return array
     */
    protected function getFilterMappings()
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [];
    }

    /**
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    protected function applyExtraFilters(QueryBuilder $query){
        return $query;
    }

    /**
     * @param string $group_code
     * @return bool
     */
    protected static function isCurrentMemberOnGroup($group_code){
        $resource_server_ctx = App::make(\models\oauth2\IResourceServerContext::class);
        $member_repository   = App::make(\models\main\IMemberRepository::class);
        $member_id           = $resource_server_ctx->getCurrentUserExternalId();
        if(is_null($member_id)) return false;
        $member              = $member_repository->getById($member_id);
        if (!is_null($member)){
            return $member->isOnGroup($group_code);
        }
        return false;
    }
}