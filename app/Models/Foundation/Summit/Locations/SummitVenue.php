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
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitVenue")
 * Class SummitVenue
 * @package models\summit
 */
class SummitVenue extends SummitGeoLocatedLocation
{

    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitVenue';
    }

    public function __construct()
    {
        parent::__construct();
        $this->rooms  = new ArrayCollection();
        $this->floors = new ArrayCollection();
    }

    /**
     * @ORM\Column(name="IsMain", type="boolean")
     * @var bool
     */
    private $is_main;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitVenueRoom", mappedBy="venue", cascade={"persist"})
     * @var SummitVenueRoom[]
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitVenueFloor", mappedBy="venue", cascade={"persist"})
     * @var SummitVenueFloor[]
     */
    private $floors;

    /**
     * @return bool
     */
    public function getIsMain()
    {
        return (bool)$this->is_main;
    }

    /**
     * @param bool $is_main
     */
    public function setIsMain($is_main)
    {
        $this->is_main = $is_main;
    }

    /**
     * @return SummitVenueRoom[]
     */
    public function getRooms(){
        return $this->rooms;
    }

    /**
     * @param int $room_id
     * @return SummitVenueRoom|null
     */
    public function getRoom($room_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($room_id)));
        $room = $this->rooms->matching($criteria)->first();
        return $room === false ? null:$room;
    }

    /**
     * @return SummitVenueFloor[]
     */
    public function getFloors(){
        return $this->floors;
    }

    /**
     * @param int $floor_id
     * @return SummitVenueFloor|null
     */
    public function getFloor($floor_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($floor_id)));
        $floor = $this->floors->matching($criteria)->first();
        return $floor === false ? null:$floor;
    }

}