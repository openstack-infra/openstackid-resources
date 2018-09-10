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
use Doctrine\ORM\NoResultException;
use models\exceptions\ValidationException;
use models\main\Member;
use models\main\SummitMemberSchedule;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAttendee")
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitAttendeeRepository")
 * Class SummitAttendee
 * @package models\summit
 */
class SummitAttendee extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="SharedContactInfo", type="boolean")
     * @var bool
     */
    private $share_contact_info;

    /**
     * @ORM\Column(name="SummitHallCheckedIn", type="boolean")
     * @var \DateTime
     */
    private $summit_hall_checked_in;

    /**
     * @ORM\Column(name="SummitHallCheckedInDate", type="datetime")
     * @var \DateTime
     */
    private $summit_hall_checked_in_date;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member;

    /**
     * @return \DateTime
     */
    public function getSummitHallCheckedInDate(){
        return $this->summit_hall_checked_in_date;
    }

    /**
     * @return bool
     */
    public function getSummitHallCheckedIn(){
        return (bool)$this->summit_hall_checked_in;
    }

    /**
     * @param bool $summit_hall_checked_in
     */
    public function setSummitHallCheckedIn($summit_hall_checked_in){
        $this->summit_hall_checked_in = $summit_hall_checked_in;
    }

    /**
     * @param \DateTime $summit_hall_checked_in_date
     */
    public function setSummitHallCheckedInDate(\DateTime $summit_hall_checked_in_date){
        $this->summit_hall_checked_in_date = $summit_hall_checked_in_date;
    }

    /**
     * @return boolean
     */
    public function getSharedContactInfo()
    {
        return $this->share_contact_info;
    }

    /**
     * @param boolean $share_contact_info
     */
    public function setShareContactInfo($share_contact_info)
    {
        $this->share_contact_info = $share_contact_info;
    }

    /**
     * @return int
     */
    public function getMemberId(){
        try{
            return $this->member->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasMember(){
        return $this->getMemberId() > 0;
    }

    /**
     * @ORM\OneToMany(targetEntity="SummitAttendeeTicket", mappedBy="owner", cascade={"persist"}, orphanRemoval=true)
     * @var SummitAttendeeTicket[]
     */
    private $tickets;

    /**
     * @return SummitAttendeeTicket[]
     */
    public function getTickets(){
        return $this->tickets;
    }

    /**
     * @param SummitAttendeeTicket $ticket
     */
    public function addTicket(SummitAttendeeTicket $ticket){
        $this->tickets->add($ticket);
        $ticket->setOwner($this);
    }

    /**
     * @return Member
     */
    public function getMember(){
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member){
        $this->member = $member;
    }

    use SummitOwned;

    public function __construct()
    {
        parent::__construct();
        $this->share_contact_info     = false;
        $this->summit_hall_checked_in = false;
        $this->tickets                = new ArrayCollection();
    }

    /**
     * @return SummitEventFeedback[]
     */
    public function getEmittedFeedback(){

        return $this->member->getFeedback()->matching
        (
            Criteria::create()->orderBy(["id" => Criteria::ASC])
        );
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     * @deprecated use Member::add2Schedule instead
     */
    public function add2Schedule(SummitEvent $event)
    {
        $this->member->add2Schedule($event);
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     * @deprecated use Member::removeFromSchedule instead
     */
    public function removeFromSchedule(SummitEvent $event)
    {
       $this->member->removeFromSchedule($event);
    }

    /**
     * @param SummitEvent $event
     * @return bool
     * @deprecated use Member::isOnSchedule instead
     */
    public function isOnSchedule(SummitEvent $event)
    {
        return $this->member->isOnSchedule($event);
    }

    /**
     * @param SummitEvent $event
     * @return null| SummitMemberSchedule
     * @deprecated use Member::getScheduleByEvent instead
     */
    public function getScheduleByEvent(SummitEvent $event){
        return $this->member->getScheduleByEvent($event);
    }

    /**
     * @return SummitMemberSchedule[]
     * @deprecated use Member::getScheduleBySummit instead
     */
    public function getSchedule(){
        return $this->member->getScheduleBySummit($this->summit);
    }

    /**
     * @return int[]
     * @deprecated use Member::getScheduledEventsIds instead
     */
    public function getScheduledEventsIds(){
        return $this->member->getScheduledEventsIds($this->summit);
    }

    /**
     * @param int $event_id
     * @return null|RSVP
     * @deprecated use Member::getRsvpByEvent instead
     */
    public function getRsvpByEvent($event_id){
       return $this->member->getRsvpByEvent($event_id);
    }

    /**
     * @param int $ticket_id
     * @return SummitAttendeeTicket
     */
    public function getTicketById($ticket_id){
        $ticket = $this->tickets->matching(
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("id", $ticket_id))
        )->first();
        return $ticket ? $ticket : null;
    }

    /**
     * @param SummitAttendeeTicket $ticket
     * @return $this
     */
    public function removeTicket(SummitAttendeeTicket $ticket){
        $this->tickets->removeElement($ticket);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTickets(){
        return $this->tickets->count() > 0;
    }

}