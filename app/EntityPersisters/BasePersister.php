<?php namespace App\EntityPersisters;
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
use LaravelDoctrine\ORM\Facades\Registry;
/**
 * Class BasePersister
 * @package App\EntityPersisters
 */
abstract class BasePersister
{
    /**
     * @param string $sql
     * @param array $bindings
     */
    protected static function insert($sql, array $bindings, array $types){
        $em = Registry::getManager('ss');
        $em->getConnection()->executeUpdate($sql, $bindings, $types);
    }
}