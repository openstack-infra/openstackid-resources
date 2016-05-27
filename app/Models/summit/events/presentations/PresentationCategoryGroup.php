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
 * @ORM\Table(name="PresentationCategory")
 * @package models\summit
 */
class PresentationCategoryGroup extends SilverstripeBaseModel
{

    protected static $array_mappings = array
    (
        'ID'          => 'id:json_int',
        'Name'        => 'name:json_string',
        'Color'       => 'color:json_string',
        'Description' => 'description:json_string',
    );


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

    /**
     * @var PresentationCategory[]
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory", mappedBy="groups")
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    /**
     * @return PresentationCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $values = parent::toArray();

        $color  = isset($values['color']) ? $values['color']:'';
        if(empty($color))
            $color = 'f0f0ee';
        if (strpos($color,'#') === false) {
            $color = '#'.$color;
        }
        $values['color'] = $color;
        $categories = array();
        foreach($this->getCategories()->getKeys() as $c)
        {
            array_push($categories, intval($c->ID));
        }
        $values['tracks'] = $categories;
        return $values;
    }
}