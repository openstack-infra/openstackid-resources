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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use LaravelDoctrine\ORM\Facades\Registry;

/***
 * @ORM\MappedSuperclass
 * Class SilverstripeBaseModel
 * @package models\utils
 */
class SilverstripeBaseModel implements IEntity
{
    const DefaultTimeZone = 'America/Chicago';

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getLastEdited()
    {
        return $this->last_edited;
    }

    /**
     * @param \DateTime $last_edited
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
        $now               = new \DateTime('now', new \DateTimeZone(self::DefaultTimeZone));
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

    /**
     * @param string $sql
     * @return mixed
     */
    protected function prepareRawSQL($sql){
        return Registry::getManager(self::EntityManager)->getConnection()->prepare($sql);
    }

    /**
     * @return EntityManager
     */
    protected function getEM(){
        return Registry::getManager(self::EntityManager);
    }

    const EntityManager = 'ss';
}