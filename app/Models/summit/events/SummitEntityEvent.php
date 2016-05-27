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

use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEntityEvent")
 * Class SummitEntityEvent
 * @package models\summit
 */

final class SummitEntityEvent extends SilverstripeBaseModel
{
    protected static $array_mappings = array
    (
        'ID'              => 'id:json_int',
        'EntityID'        => 'entity_id:json_int',
        'EntityClassName' => 'entity_class:json_string',
        'Created'         => 'created:datetime_epoch',
        'Type'            => 'type',
    );

    use SummitOwned;

    /**
     * @ORM\Column(name="EntityID", type="integer")
     */
    protected $entity_id;

    /**
     * @return int
     */
    public function getEntityID(){return $this->entity_id;}

    /**
     * @ORM\Column(name="EntityClassName", type="string")
     */
    private $entity_class_name;

    /**
     * @return string
     */
    public function getEntityClassName(){return $this->entity_class_name;}

    /**
     * @ORM\Column(name="Created", type="datetime")
     */
    protected $created;

    /**
     * @return \DateTime
     */
    public function getCreated(){ return $this->created; }

    /**
     * @ORM\Column(name="Type", type="string")
     */
    private $type;

    /**
     * @return string
     */
    public function getType(){return $this->type;}
}