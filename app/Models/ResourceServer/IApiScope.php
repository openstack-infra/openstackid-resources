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


/**
 * Interface IApiScope
 * http://tools.ietf.org/html/rfc6749#section-3.3
 * @package oauth2\models
 */
interface IApiScope {
    /**
     * @return string
     */
    public function getShortDescription();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return IApi
     */
    public function api();
}