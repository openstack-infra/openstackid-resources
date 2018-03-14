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
use models\main\File;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="SummitLocationImage")
 * @package models\summit
 */
class SummitLocationImage extends SilverstripeBaseModel
{
    const TypeMap   = 'SummitLocationMap';
    const TypeImage = 'SummitLocationImage';
    /**
     * @ORM\Column(name="Name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="Description", type="string")
     */
    protected $description;

    /**
     * @ORM\Column(name="`Order`", type="integer")
     */
    protected $order;

    /**
     * @ORM\Column(name="ClassName", type="string")
     */
    protected $class_name;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="PictureID", referencedColumnName="ID", onDelete="CASCADE")
     * @var File
     */
    protected $picture;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitGeoLocatedLocation", inversedBy="images")
     * @ORM\JoinColumn(name="LocationID", referencedColumnName="ID", onDelete="CASCADE")
     * @var SummitGeoLocatedLocation
     */
    protected $location;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return File
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param File $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return SummitAbstractLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return int
     */
    public function getLocationId(){
        try{
            return !is_null($this->location) ? $this->location->getId() : 0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param SummitAbstractLocation $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->class_name;
    }

    /**
     * @param string $class_name
     */
    public function setClassName($class_name)
    {
        $this->class_name = $class_name;
    }

    /**
     * @return bool
     */
    public function hasPicture(){
        return $this->getPictureId() > 0;
    }

    /**
     * @return int
     */
    public function getPictureId(){
        try{
            return !is_null($this->picture) ? $this->picture->getId() : 0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    public function clearLocation(){
        $this->location = null;
    }

    public function clearPicture(){
        $this->picture = null;
    }
}