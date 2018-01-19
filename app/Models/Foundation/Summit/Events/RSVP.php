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

use models\main\Member;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="RSVP")
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineRSVPRepository")
 * Class RSVP
 * @package models\summit
 */
class RSVP extends SilverstripeBaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->answers = new ArrayCollection();
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", inversedBy="rsvp", fetch="LAZY")
     * @ORM\JoinColumn(name="SubmittedByID", referencedColumnName="ID")
     * @var Member
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitEvent", inversedBy="rsvp", fetch="LAZY")
     * @ORM\JoinColumn(name="EventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    private $event;


    /**
     * @ORM\Column(name="SeatType", type="string")
     */
    protected $seat_type;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\RSVPAnswer", mappedBy="rsvp", cascade={"persist", "remove"})
     * @var RSVPAnswer[]
     */
    protected $answers;

    /**
     * @return ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param ArrayCollection $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner(Member $owner){
        $this->owner = $owner;
    }


    /**
     * @return SummitEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return int
     */
    public function getEventId(){
        try{
            return $this->event->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasOwner(){
        return $this->getOwnerId() > 0;
    }

    /**
     * @return int
     */
    public function getOwnerId(){
        try{
            return $this->owner->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param SummitEvent $event
     */
    public function setEvent(SummitEvent $event){
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getSeatType()
    {
        return $this->seat_type;
    }

    /**
     * @param mixed $seat_type
     */
    public function setSeatType($seat_type)
    {
        $this->seat_type = $seat_type;
    }

}