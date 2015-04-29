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

namespace repositories\resource_server;

use models\resource_server\ApiEndpoint;
use models\resource_server\IApiEndpoint;
use models\utils\IEntity;
use Illuminate\Support\Facades\DB;
use models\resource_server\IApiEndpointRepository;

/**
 * Class EloquentApiEndpointRepository
 * @package repositories\resource_server
 */
class EloquentApiEndpointRepository implements IApiEndpointRepository {

    /**
     * @var IEntity
     */
    protected $entity;


    /**
     * @param IApiEndpoint  $endpoint
     */
    public function __construct(IApiEndpoint $endpoint){
        $this->entity = $endpoint;
    }
    /**
     * @param string $url
     * @param string $http_method
     * @return IApiEndpoint
     */
    public function getApiEndpointByUrlAndMethod($url, $http_method)
    {
        return $this->entity->Filter(array( array(
            'name'=>'route',
            'op' => '=',
            'value'=> $url
        ), array(
            'name'=>'http_method',
            'op' => '=',
            'value'=> $http_method
        )))->firstOrFail();
    }

    /**
     * @param int $id
     * @return IEntity
     */
    public function getById($id)
    {
        return $this->entity->find($id);
    }
}