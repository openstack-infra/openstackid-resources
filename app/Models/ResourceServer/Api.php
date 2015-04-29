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
 * Class Api
 * @package models\resource_server
 */
class Api extends BaseModelEloquent implements IApi {

    protected $table = 'apis';

    protected $fillable = array('name','description','active');


    /**
     * @return IApiScope[]
     */
    public function scopes()
    {
        return $this->hasMany('models\resource_server\ApiScope','api_id');
    }

    /**
     * @return IApiEndpoint[]
     */
    public function endpoints()
    {
        return $this->hasMany('models\resource_server\ApiEndpoint','api_id');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setStatus($active)
    {
        $this->active = $active;
    }
}