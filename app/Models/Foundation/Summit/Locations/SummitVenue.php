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
use models\exceptions\ValidationException;

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
        return self::ClassName;
    }

    const ClassName = 'SummitVenue';

    public function __construct()
    {
        parent::__construct();
        $this->is_main = false;
        $this->type    = self::TypeInternal;
        $this->rooms   = new ArrayCollection();
        $this->floors  = new ArrayCollection();
    }

    /**
     * @ORM\Column(name="IsMain", type="boolean")
     * @var bool
     */
    private $is_main;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitVenueRoom", mappedBy="venue", cascade={"persist"}, orphanRemoval=true)
     * @var SummitVenueRoom[]
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitVenueFloor", mappedBy="venue", cascade={"persist"}, orphanRemoval=true)
     * @var SummitVenueFloor[]
     */
    private $floors;

    public function addFloor(SummitVenueFloor $floor){
        $this->floors->add($floor);
        $floor->setVenue($this);
    }

    public function addRoom(SummitVenueRoom $room){
        $this->rooms->add($room);
        $room->setVenue($this);
    }

    /**
     * @param SummitVenueRoom $room
     * @param int $new_order
     * @throws ValidationException
     */
    public function recalculateRoomsOrder(SummitVenueRoom $room, $new_order){

        $criteria     = Criteria::create();
        $criteria->orderBy(['order'=> 'ASC']);
        $rooms        = $this->rooms->matching($criteria)->toArray();
        $rooms        = array_slice($rooms,0, count($rooms), false);
        $max_order    = count($rooms);
        $former_order =  1;
        foreach ($rooms as $r){
            if($r->getId() == $room->getId()) break;
            $former_order++;
        }

        if($new_order > $max_order)
            throw new ValidationException(sprintf("max order is %s", $max_order));

        unset($rooms[$former_order - 1]);

        $rooms = array_merge
        (
            array_slice($rooms, 0, $new_order -1 , true) ,
            [$room] ,
            array_slice($rooms, $new_order -1 , count($rooms), true)
        );

        $order = 1;
        foreach($rooms as $r){
            $r->setOrder($order);
            $order++;
        }

    }

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
        return $room === false ? null : $room;
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


    public static $metadata = [
        'class_name' => self::ClassName,
        'is_main'    => 'boolean',
        'floors'     => 'array',
        'rooms'      => 'array',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(SummitGeoLocatedLocation::getMetadata(), self::$metadata);
    }

    /**
     * @param string $floor_name
     * @return SummitVenueFloor|null
     */
    public function getFloorByName($floor_name){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', trim($floor_name)));
        $floor = $this->floors->matching($criteria)->first();
        return $floor === false ? null : $floor;
    }

    /**
     * @param int $floor_number
     * @return SummitVenueFloor|null
     */
    public function getFloorByNumber($floor_number){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('number', intval($floor_number)));
        $floor = $this->floors->matching($criteria)->first();
        return $floor === false ? null:$floor;
    }

    /**
     * @param SummitVenueFloor $floor
     * @return $this
     */
    public function removeFloor(SummitVenueFloor $floor){
        $this->floors->removeElement($floor);
        $floor->clearVenue();
        return $this;
    }

    /**
     * @param SummitVenueRoom $room
     * @return $this
     */
    public function removeRoom(SummitVenueRoom $room){
        $this->rooms->removeElement($room);
        $room->clearVenue();
        return $this;
    }
}