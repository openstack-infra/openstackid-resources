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
    protected static $array_mappings = array
    (
        'ID'    => 'id:json_int',
        'Title' => 'name:json_string',
    );

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
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategoryGroup", inversedBy="categories")
     * @ORM\JoinTable(name="PresentationCategoryGroup_Categories",
     *      joinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryGroupID", referencedColumnName="ID")}
     *      )
     */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * @return PresentationCategoryGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $values = parent::toArray();
        $groups = array();
        foreach($this->groups() as $g)
        {
            array_push($groups, intval($g->ID));
        }
        $values['track_groups'] = $groups;
        return $values;
    }
}