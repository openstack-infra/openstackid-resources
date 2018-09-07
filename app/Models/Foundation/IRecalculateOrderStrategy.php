<?php namespace App\Models;
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
use App\Models\Foundation\IOrderableEntity;
use models\exceptions\ValidationException;
/**
 * Interface IRecalculateOrderStrategy
 * @package App\Models
 */
interface IRecalculateOrderStrategy
{
    /**
     * @param array $collection
     * @param IOrderableEntity $entity
     * @param int $new_order
     * @throws ValidationException
     */
    public function recalculateOrder(array $collection , IOrderableEntity $entity, $new_order);
}