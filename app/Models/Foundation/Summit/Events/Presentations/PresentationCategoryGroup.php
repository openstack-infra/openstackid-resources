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
use Doctrine\Common\Collections\Criteria;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Class PresentationCategoryGroup
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrinePresentationCategoryGroupRepository")
 * @ORM\Table(name="PresentationCategoryGroup")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({
 *     "PresentationCategoryGroup" = "PresentationCategoryGroup",
 *     "PrivatePresentationCategoryGroup" = "PrivatePresentationCategoryGroup"
 * })
 * @package models\summit
 */
class PresentationCategoryGroup extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="Color", type="string")
     * @var string
     */
    protected $color;

    /**
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    protected $description;

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
     * @return int
     */
    public function getSummitId(){
        try {
            return is_null($this->summit) ? 0 : $this->summit->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    public function clearSummit(){
        $this->summit = null;
    }

    /**
     * @return Summit
     */
    public function getSummit(){
        return $this->summit;
    }

    public function __construct()
    {
        parent::__construct();
        $this->categories = new ArrayCollection;
    }

    /**
     * owning side
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory")
     * @ORM\JoinTable(name="PresentationCategoryGroup_Categories",
     *      joinColumns={@ORM\JoinColumn(name="PresentationCategoryGroupID", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="id")}
     * )
     * @var PresentationCategory[]
     */
    protected $categories;

    /**
     * @return PresentationCategory[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param PresentationCategory $track
     */
    public function addCategory(PresentationCategory $track){
        $track->addToGroup($this);
        $this->categories[] = $track;
    }

    /**
     * @param PresentationCategory $track
     */
    public function removeCategory(PresentationCategory $track){
        $track->removeFromGroup($this);
        $this->categories->removeElement($track);
    }

    /**
     * @param int $category_id
     * @return PresentationCategory|null
     */
    public function getCategoryById($category_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($category_id)));
        $res = $this->categories->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @param int $category_id
     * @return bool
     */
    public function hasCategory($category_id){
        return $this->getCategoryById($category_id) != null;
    }

    const ClassName = 'PresentationCategoryGroup';

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public static $metadata = [
        'class_name'  => self::ClassName,
        'id'          => 'integer',
        'summit_id'   => 'integer',
        'name'        => 'string',
        'color'       => 'string',
        'description' => 'string',
        'categories'  => 'array'
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return self::$metadata;
    }

}