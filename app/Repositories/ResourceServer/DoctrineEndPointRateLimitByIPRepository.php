<?php namespace repositories\resource_server;
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
use App\Models\ResourceServer\EndPointRateLimitByIP;
use App\Models\ResourceServer\IEndpointRateLimitByIPRepository;
use App\Repositories\DoctrineRepository;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Support\Facades\Log;
/**
 * Class DoctrineEndPointRateLimitByIPRepository
 * @package repositories\resource_server
 */
final class DoctrineEndPointRateLimitByIPRepository
    extends DoctrineRepository
    implements IEndpointRateLimitByIPRepository
{
    /**
     * @param string $ip
     * @param string $route
     * @param string $http_method
     * @return EndPointRateLimitByIP
     */
    public function getByIPRouteMethod($ip, $route, $http_method)
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select("c")
                ->from(\App\Models\ResourceServer\EndPointRateLimitByIP::class, "c")
                ->where('c.route = :route')
                ->andWhere('c.http_method = :http_method')
                ->andWhere('c.ip = :ip')
                ->andWhere('c.active = 1')
                ->setParameter('ip', trim($ip))
                ->setParameter('route', trim($route))
                ->setParameter('http_method', trim($http_method))
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch(\Exception $ex){
            Log::error($ex);
            return null;
        }
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return EndPointRateLimitByIP::class;
    }

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
    protected function applyExtraFilters(QueryBuilder $query)
    {
       return $query;
    }
}