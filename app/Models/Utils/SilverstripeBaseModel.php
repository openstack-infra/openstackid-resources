<?php namespace models\utils;
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

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Registry;
/***
 * @ORM\MappedSuperclass
 * Class SilverstripeBaseModel
 * @package models\utils
 */
class SilverstripeBaseModel implements IEntity
{
    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getLastEdited()
    {
        return $this->last_edited;
    }

    /**
     * @param mixed $last_edited
     */
    public function setLastEdited($last_edited)
    {
        $this->last_edited = $last_edited;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ID", type="integer", unique=true, nullable=false)
     */
    protected $id;

    /**
     * @ORM\Column(name="Created", type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(name="LastEdited", type="datetime")
     */
    protected $last_edited;

    /**
    * @return int
    */
    public function getIdentifier()
    {
        return (int)$this->id;
    }

    public function getId(){
        return $this->getIdentifier();
    }

    public function __construct()
    {

        $now               = new \DateTime();
        $this->created     = $now;
        $this->last_edited = $now;
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder(){
        return Registry::getManager(self::EntityManager)->createQueryBuilder();
    }

    /**
     * @param string $dql
     * @return Query
     */
    protected function createQuery($dql){
        return Registry::getManager(self::EntityManager)->createQuery($dql);
    }

    const EntityManager = 'ss';
}