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

namespace models\utils;

/**
 * Class EloquentBaseRepository
 * @package models\utils
 */
abstract class EloquentBaseRepository implements IBaseRepository
{
    /**
     * @var IEntity
     */
    protected $entity;
    /**
     * @param int $id
     * @return \models\utils\IEntity
     */
    public function getById($id)
    {
        return $this->entity->find($id);
    }

    /**
     * @param IEntity $entity
     * @return void
     */
    public function add($entity)
    {
        $entity->save();
    }


    /**
     * @param IEntity $entity
     * @return void
     */
    public function delete($entity)
    {
        $entity->delete();
    }

    /**
     * @return IEntity[]
     */
    public function getAll()
    {
        return $this->entity->all()->all();
    }
}