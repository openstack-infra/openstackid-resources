<?php namespace models\main;
/**
 * Copyright 2017 OpenStack Foundation
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
use models\summit\SummitEvent;
use models\utils\IEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="Member_Schedule")
 * Class SummitMemberSchedule
 * @package models\main
 */
class SummitMemberSchedule implements IEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ID", type="integer", unique=true, nullable=false)
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }


    public function clearOwner(){
        $this->member = null;
        $this->event  = null;
    }

    /**
     * @return SummitEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param SummitEvent $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return $this->id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="schedule")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID", nullable=true )
     * @var Member
     */
    private $member;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitEvent")
     * @ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    private $event;

}