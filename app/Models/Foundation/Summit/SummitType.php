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
 * @ORM\Table(name="SummitType")
 * Class SummitType
 * @package models\summit
 */
class SummitType extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="FriendlyName", type="string")
     */
    private $friendly_name;

    /**
     * @return mixed
     */
    public function getFriendlyName()
    {
        return $this->friendly_name;
    }

    /**
     * @param mixed $friendly_name
     */
    public function setFriendlyName($friendly_name)
    {
        $this->friendly_name = $friendly_name;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * @param mixed $audience
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;
    }

    /**
     * @ORM\Column(name="Color", type="string")
     */
    private $color;

    /**
     * @ORM\Column(name="Type", type="string")
     */
    private $type;

    /**
     * @ORM\Column(name="Description", type="string")
     */
    private $description;

    /**
     * @ORM\Column(name="Audience", type="string")
     */
    private $audience;
}