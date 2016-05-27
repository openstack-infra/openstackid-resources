<?php namespace repositories;
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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use LaravelDoctrine\ORM\Facades\Registry;use models\utils\IBaseRepository;
use models\utils\IEntity;

/**
 * Class DoctrineRepository
 * @package repositories
 */
abstract class DoctrineRepository extends EntityRepository implements IBaseRepository
{
    protected static $em_name = 'default';
    /**
     * Initializes a new <tt>EntityRepository</tt>.
     *
     * @param EntityManager         $em    The EntityManager to use.
     * @param ClassMetadata $class The class descriptor.
     */
    public function __construct($em, ClassMetadata $class)
    {
        $em = Registry::getManager(static::$em_name);
        parent::__construct($em, $class);
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    /**
     * @param IEntity $entity
     * @return void
     */
    public function add($entity)
    {
        $this->_em->persist($entity);
    }

    /**
     * @param IEntity $entity
     * @return void
     */
    public function delete($entity)
    {
        $this->_em->remove($entity);
    }

    /**
     * @return IEntity[]
     */
    public function getAll()
    {
        return $this->findAll();
    }

}