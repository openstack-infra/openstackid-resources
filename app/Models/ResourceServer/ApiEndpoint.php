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

namespace models\resource_server;

use models\utils\BaseModelEloquent;
/**
 * Class ApiEndpoint
 * @package models\resource_server
 */
class ApiEndpoint extends BaseModelEloquent implements IApiEndpoint {

    protected $table = 'api_endpoints';

    protected $fillable = array( 'description','active','allow_cors', 'allow_credentials', 'name','route', 'http_method', 'api_id', 'rate_limit');

    /**
     * @return IApi
     */
    public function api()
    {
        return $this->belongsTo('models\resource_server\Api', 'api_id');
    }

    /**
     * @return IApiScope[]
     */
    public function scopes()
    {
        return $this->belongsToMany('models\resource_server\ApiScope','endpoint_api_scopes','api_endpoint_id','scope_id');
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getHttpMethod(){
        return $this->http_method;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function setHttpMethod($http_method)
    {
        $this->http_method = $http_method;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        $scope = '';
        foreach($this->scopes()->get() as $s){
            if(!$s->active) continue;
            $scope = $scope .$s->name.' ';
        }
        $scope = trim($scope);
        return $scope;
    }

    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setStatus($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name= $name;
    }

    /**
     * @return bool
     */
    public function supportCORS()
    {
        return $this->allow_cors;
    }

    /**
     * @return bool
     */
    public function supportCredentials()
    {
        return (bool)$this->allow_credentials;
    }
}