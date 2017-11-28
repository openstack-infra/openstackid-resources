<?php namespace repositories\main;
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
use models\main\Group;
use models\main\IGroupRepository;

/**
 * Class DoctrineGroupRepository
 * @package repositories\main
 */
final class DoctrineGroupRepository
    extends SilverStripeDoctrineRepository
    implements IGroupRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
       return Group::class;
    }
}