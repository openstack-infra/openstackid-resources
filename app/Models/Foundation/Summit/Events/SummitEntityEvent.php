<?php namespace models\summit;
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

use models\main\Member;
use models\utils\IEntity;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEntityEvent")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineSummitEntityEventRepository")
 * Class SummitEntityEvent
 * @package models\summit
 */
class SummitEntityEvent extends SilverstripeBaseModel
{
    use SummitOwned;

    /**
     * @ORM\Column(name="EntityID", type="integer")
     */
    protected $entity_id;

    /**
     * @param int $entity_id
     */
    public function setEntityId($entity_id){
        $this->entity_id = $entity_id;
    }

    /**
     * @return int
     */
    public function getEntityId(){return $this->entity_id;}

    /**
     * @ORM\Column(name="EntityClassName", type="string")
     */
    private $entity_class_name;

    /**
     * @return string
     */
    public function getEntityClassName(){return $this->entity_class_name;}

    /**
     * @param string $entity_class_name
     */
    public function setEntityClassName($entity_class_name){$this->entity_class_name = $entity_class_name;}

    /**
     * @ORM\Column(name="Type", type="string")
     */
    private $type;

    /**
     * @return string
     */
    public function getType(){return $this->type;}

    /**
     * @param string $type
     */
    public function setType($type){$this->type = $type;}

    /**
     * @ORM\Column(name="Metadata", type="string")
     */
    private $metadata;

    /**
     * @param string $metadata
     */
    public function setMetadata($metadata){
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getMetadata(){
        return !empty($this->metadata) ? json_decode($this->metadata, true) : array();
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", cascade={"persist"})
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    private $owner;

    /**
     * @return int
     */
    public function getOwnerId(){
        try{
            return $this->owner->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasOwner(){
        return $this->getOwnerId() > 0;
    }

    /**
     * @return Member
     */
    public function getOwner(){
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner(Member $owner){
        $this->owner = $owner;
    }

    /**
     * @return string
     */
    public function getKey(){
        return sprintf("%s.%s", $this->entity_class_name, $this->entity_id);
    }

    /**
     * @var IEntity
     */
    private $entity;

    /**
     * @return IEntity
     */
    public function getEntity(){
        return $this->entity;
    }

    /**
     * @param IEntity $entity
     * @return IEntity
     */
    public function registerEntity(IEntity $entity){
        $this->entity = $entity;
        return $this->entity;
    }

}