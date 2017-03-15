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
 * Class PresentationCategory
 * @ORM\Entity
 * @ORM\Table(name="PresentationCategory")
 * @package models\summit
 */
class PresentationCategory extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="Code", type="string")
     * @var string
     */
    private $code;

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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Summit", inversedBy="presentation_categories")
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

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategoryGroup", mappedBy="categories")
     * @var PresentationCategoryGroup[]
     */
    private $groups;

    public function __construct()
    {
        parent::__construct();
        $this->groups = new ArrayCollection();
    }

    /**
     * @return PresentationCategoryGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

}