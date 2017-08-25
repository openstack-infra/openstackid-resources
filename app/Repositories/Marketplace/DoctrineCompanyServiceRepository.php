<?php namespace App\Repositories\Marketplace;
/**
 * Copyright 2017 OpenStack Foundation
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
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\QueryBuilder;
use utils\DoctrineJoinFilterMapping;
/**
 * Class DoctrineCompanyServiceRepository
 * @package App\Repositories\Marketplace
 */
abstract class DoctrineCompanyServiceRepository extends SilverStripeDoctrineRepository
{

    /**
     * @param QueryBuilder $query
     * @return QueryBuilder
     */
    protected function applyExtraFilters(QueryBuilder $query){
        $query->where('e.is_active = 1');
        return $query;
    }

    /**
     * @return array
     */
    protected function getFilterMappings()
    {
        return [
            'name'    => 'e.name',
            'company' => new DoctrineJoinFilterMapping
            (
                'e.company',
                'c',
                "c.name :operator ':value'"
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'id'   => 'e.id',
            'name' => 'e.name',
        ];
    }
}