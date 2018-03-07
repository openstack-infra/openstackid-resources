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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use models\exceptions\ValidationException;
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
     * @ORM\Column(name="Name", type="string")
     */
    private $name;

    /**
     * @ORM\Column(name="Description", type="string")
     */
    private $description;

    /**
     * @ORM\Column(name="Number", type="integer")
     */
    private $number;

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
     * @ORM\ManyToOne(targetEntity="models\main\File", fetch="EAGER", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="ImageID", referencedColumnName="ID")
     * @var File
     */
    private $image;

    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitVenueFloor';
    }

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
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return SummitVenueRoom[]
     */
    public function getRooms(){
        return $this->rooms;
    }

    /**
     * @param int $room_id
     * @return SummitVenueRoom
     */
    public function getRoom($room_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($room_id)));
        $room = $this->rooms->matching($criteria)->first();
        return $room === false ? null : $room;
    }

    public function __construct()
    {
        parent::__construct();
        $this->rooms = new ArrayCollection();
    }

    /**
     * @param SummitVenue|null $venue
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;
    }

    public function clearVenue(){
        $this->venue = null;
    }

    /**
     * @param File $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @param SummitVenueRoom $room
     */
    public function addRoom(SummitVenueRoom $room){
        $this->rooms->add($room);
        $room->setFloor($this);
    }

    /**
     * @param SummitVenueRoom $room
     */
    public function removeRoom(SummitVenueRoom $room){
        $this->rooms->removeElement($room);
        $room->clearFloor();
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

}