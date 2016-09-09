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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitVenueRoom")
 * Class SummitVenueRoom
 * @package models\summit
 */
class SummitVenueRoom extends SummitAbstractLocation
{
    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitVenueRoom';
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
     * @return bool
     */
    public function hasVenue(){
        return $this->getVenueId() > 0;
    }

    /**
     * @return SummitVenueFloor
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @return int
     */
    public function getFloorId(){
        try{
            return $this->floor->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasFloor(){
        return $this->getFloorId() > 0;
    }

    /**
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param int $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    /**
     * @return boolean
     */
    public function isOverrideBlackouts()
    {
        return $this->override_blackouts;
    }

    /**
     * @param boolean $override_blackouts
     */
    public function setOverrideBlackouts($override_blackouts)
    {
        $this->override_blackouts = $override_blackouts;
    }
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitVenue", inversedBy="rooms")
     * @ORM\JoinColumn(name="VenueID", referencedColumnName="ID")
     * @var SummitVenue
     */
    private $venue;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitVenueFloor", inversedBy="rooms")
     * @ORM\JoinColumn(name="FloorID", referencedColumnName="ID")
     * @var SummitVenueFloor
     */
    private $floor;

    /**
     * @ORM\Column(name="Capacity", type="integer")
     * @var int
     */
    private $capacity;

    /**
     * @ORM\Column(name="OverrideBlackouts", type="boolean")
     * @var bool
     */
    private $override_blackouts;

    /**
     * @ORM\OneToMany(targetEntity="RoomMetricType", mappedBy="room", cascade={"persist"})
     */
    private $metrics;

    /**
     * @return ArrayCollection
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @param ArrayCollection $metrics
     */
    public function setMetrics($metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * SummitVenueRoom constructor.
     */
    public function __construct()
    {
        $this->metrics = new ArrayCollection();
    }

    /**
     * @param int $type_id
     * @return RoomMetricType
     */
    public function getMetricByType($type_id){

        $criteria = Criteria::create()->where(Criteria::expr()->eq("id", $type_id));
        return $this->metrics->matching($criteria)->first();
    }
}