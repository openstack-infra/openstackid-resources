<?php namespace services\utils;

/**
 * Copyright 2016 OpenStack Foundation
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
use libs\utils\ITransactionService;
use Closure;
use LaravelDoctrine\ORM\Facades\Registry;

/**
 * Class DoctrineTransactionService
 * @package services\utils
 */
final class DoctrineTransactionService implements ITransactionService
{

    /**
     * @var string
     */
    private $manager_name;

    /**
     * DoctrineTransactionService constructor.
     * @param string $manager_name
     */
    public function __construct($manager_name)
    {
        $this->manager_name = $manager_name;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  Closure $callback
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(Closure $callback)
    {
        $em = Registry::getManager($this->manager_name);
        try {
            $em->getConnection()->beginTransaction(); // suspend auto-commit
            $result = $callback($this);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }
        return $result;
    }
}