<?php namespace App\Models\ResourceServer;

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

use models\utils\IEntity;
use Doctrine\ORM\Mapping AS ORM;

/***
 * @ORM\MappedSuperclass
 * Class ResourceServerEntity
 * @package App\Models\ResourceServer
 */
class ResourceServerEntity implements IEntity
{

    const DefaultTimeZone = 'America/Chicago';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer", unique=true, nullable=false)
     */
    protected $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
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

    public function __construct()
    {
        $now               = new \DateTime('now', new \DateTimeZone(self::DefaultTimeZone));
        $this->created     = $now;
        $this->last_edited = $now;
    }
}