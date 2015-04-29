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

namespace App\Http\Controllers;

use models\oauth2\IResourceServerContext;
use models\marketplace\IPublicCloudServiceRepository;

/**
 * Class OAuth2PublicCloudApiController
 */
final class OAuth2PublicCloudApiController extends OAuth2CloudApiController {

    public function __construct (IPublicCloudServiceRepository $repository, IResourceServerContext $resource_server_context){
        parent::__construct($resource_server_context);
        $this->repository = $repository;
    }

}