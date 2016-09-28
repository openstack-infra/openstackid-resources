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
use models\main\Member;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAttendee")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineSummitAttendeeRepository")
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
     * @ORM\OneToMany(targetEntity="SummitAttendeeSchedule", mappedBy="attendee", cascade={"persist"}, orphanRemoval=true)
     * @var SummitAttendeeSchedule[]
     */
    private $schedule;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member;

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
     * @ORM\OneToMany(targetEntity="SummitAttendeeTicket", mappedBy="owner", cascade={"persist"})
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
        $this->schedule               = new ArrayCollection();
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
     */
    public function add2Schedule(SummitEvent $event)
    {
        if($this->isOnSchedule($event))
            throw new ValidationException
            (
                sprintf('Event %s already belongs to attendee %s schedule.', $event->getId(), $this->getId())
            );

        $schedule = new SummitAttendeeSchedule;

        $schedule->setAttendee($this);
        $schedule->setEvent($event);
        $schedule->setIsCheckedIn(false);
        $this->schedule->add($schedule);
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     */
    public function removeFromSchedule(SummitEvent $event)
    {
        $schedule = $this->getScheduleByEvent($event);

        if(is_null($schedule))
            throw new ValidationException
            (
                sprintf('Event %s does not belongs to attendee %s schedule.', $event->getId(), $this->getId())
            );
        $this->schedule->removeElement($schedule);
        $schedule->clearAttendee();
    }

    /**
     * @param SummitEvent $event
     * @return bool
     */
    public function isOnSchedule(SummitEvent $event)
    {
        $sql = <<<SQL
SELECT COUNT(SummitEventID) AS QTY 
FROM SummitAttendee_Schedule 
INNER JOIN SummitEvent ON SummitEvent.ID = SummitAttendee_Schedule.SummitEventID
WHERE SummitAttendeeID = :attendee_id AND SummitEvent.Published = 1 AND SummitEvent.ID = :event_id
SQL;

        $stmt = $this->prepareRawSQL($sql);
        $stmt->execute([
            'attendee_id' => $this->getId(),
            'event_id'    => $event->getId()
        ]);
        $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return count($res) > 0 ? intval($res[0]) > 0 : false;
    }

    /**
     * @param SummitEvent $event
     * @return null| SummitAttendeeSchedule
     */
    public function getScheduleByEvent(SummitEvent $event){

        $query = $this->createQuery("SELECT s from models\summit\SummitAttendeeSchedule s 
        JOIN s.attendee a 
        JOIN s.event e    
        WHERE a.id = :attendee_id and e.published = 1 and e.id = :event_id
        ");
        return $query
            ->setParameter('attendee_id', $this->getIdentifier())
            ->setParameter('event_id', $event->getIdentifier())
            ->getSingleResult();
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     */
    public function checkIn(SummitEvent $event)
    {
        $schedule = $this->getScheduleByEvent($event);

        if(is_null($schedule))
            throw new ValidationException(sprintf('Event %s does not belongs to attendee %s schedule.', $event->ID, $this->ID));
        $schedule->setIsCheckedIn(true);
    }

    /**
     * @return SummitAttendeeSchedule[]
     */
    public function getSchedule(){
        return $this->schedule;
    }

    /**
     * @return int[]
     */
    public function getScheduledEventsIds(){
        $sql = <<<SQL
SELECT SummitEventID 
FROM SummitAttendee_Schedule 
INNER JOIN SummitEvent ON SummitEvent.ID = SummitAttendee_Schedule.SummitEventID
WHERE SummitAttendeeID = :attendee_id AND SummitEvent.Published = 1
SQL;

        $stmt = $this->prepareRawSQL($sql);
        $stmt->execute(['attendee_id' => $this->getId()]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

}