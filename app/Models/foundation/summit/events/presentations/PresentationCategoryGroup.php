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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class PresentationCategoryGroup
 * @ORM\Entity
 * @ORM\Table(name="PresentationCategoryGroup")
 * @package models\summit
 */
class PresentationCategoryGroup extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="Color", type="string")
     * @var string
     */
    private $color;

    /**
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    private $description;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Summit", inversedBy="category_groups")
     * @ORM\JoinColumn(name="SummitID", referencedColumnName="ID")
     * @var Summit
     */
    protected $summit;

    public function setSummit($summit){
        $this->summit = $summit;
    }

    /**
     * @return Summit
     */
    public function getSummit(){
        return $this->summit;
    }



    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory", inversedBy="groups")
     * @ORM\JoinTable(name="PresentationCategoryGroup_Categories",
     *      joinColumns={@ORM\JoinColumn(name="PresentationCategoryGroupID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")}
     * )
     * @var PresentationCategory[]
     */
    private $categories;

    /**
     * @return PresentationCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

}