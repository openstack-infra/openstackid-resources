<?php namespace repositories\resource_server;
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

use App\Models\ResourceServer\IApiEndpoint;
use App\Models\ResourceServer\IApiEndpointRepository;
use repositories\DoctrineRepository;
use Illuminate\Support\Facades\Log;

/**
 * Class DoctrineApiEndpointRepository
 * @package repositories\resource_server
 */
final class DoctrineApiEndpointRepository extends DoctrineRepository implements IApiEndpointRepository
{

    /**
     * @param string $url
     * @param string $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url, $http_method)
    {
        try {
            return $this->getEntityManager()->createQueryBuilder()
                ->select("e")
                ->from(\App\Models\ResourceServer\ApiEndpoint::class, "e")
                ->where('e.route = :route')
                ->andWhere('e.http_method = :http_method')
                ->setParameter('route', trim($url))
                ->setParameter('http_method', trim($http_method))
                ->setCacheable(true)
                ->setCacheRegion('resource_server_region')
                ->getQuery()
                ->getOneOrNullResult();
        }
        catch(\Exception $ex){
            Log::error($ex);
            return null;
        }
    }
}