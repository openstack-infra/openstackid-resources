<?php namespace repositories\main;
/**
 * Copyright 2018 OpenStack Foundation
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
use models\main\IOrganizationRepository;
use models\main\Organization;
/**
 * Class DoctrineOrganizationRepository
 * @package repositories\main
 */
final class DoctrineOrganizationRepository
    extends SilverStripeDoctrineRepository
    implements IOrganizationRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return Organization::class;
    }
}