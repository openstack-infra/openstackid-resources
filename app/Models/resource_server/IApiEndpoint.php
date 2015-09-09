<?php namespace models\resource_server;

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
use models\utils\IEntity;

/**
 * Interface IApiEndpoint
 * @package models\resource_server
 */
interface IApiEndpoint extends IEntity
{

    /**
     * @return string
     */
    public function getRoute();

    /**
     * @return string
     */
    public function getHttpMethod();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $route
     * @return void
     */
    public function setRoute($route);

    /**
     * @param string $http_method
     * @return void
     */
    public function setHttpMethod($http_method);

    /**
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getScope();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param bool $active
     * @return void
     */
    public function setStatus($active);

    /**
     * @return bool
     */
    public function supportCORS();

    /**
     * @return bool
     */
    public function supportCredentials();

    /**
     * @return IApi
     */
    public function api();

    /**
     * @return IApiScope[]
     */
    public function scopes();

}