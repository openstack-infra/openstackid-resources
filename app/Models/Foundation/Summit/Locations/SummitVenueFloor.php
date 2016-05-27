<?php namespace models\summit;
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

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use models\main\File;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitVenueFloor")
 * Class Summit
 * @package models\summit
 */
class SummitVenueFloor extends SilverstripeBaseModel
{
    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitVenueFloor';
    }

    /**
     * @ORM\Column(name="Name", type="string")
     */
    private $name;

    /**
     * @ORM\Column(name="Description", type="string")
     */
    private $description;

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
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return SummitVenue
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @return int
     */
    public function getVenueId(){
        try{
            return $this->venue->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @ORM\Column(name="Number", type="integer")
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File", fetch="EAGER")
     * @ORM\JoinColumn(name="ImageID", referencedColumnName="ID")
     * @var File
     */
    private $image;

    /**
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     *
     * @ORM\ManyToOne(targetEntity="models\summit\SummitVenue", inversedBy="floors")
     * @ORM\JoinColumn(name="VenueID", referencedColumnName="ID")
     * @var SummitVenue
     */
    private $venue;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitVenueRoom", mappedBy="floor", cascade={"persist"})
     * @var SummitVenueRoom[]
     */
    private $rooms;

    /**
     * @return SummitVenueRoom[]
     */
    public function getRooms(){
        return $this->rooms;
    }

    public function __construct()
    {
        parent::__construct();
        $this->rooms = new ArrayCollection();
    }

}