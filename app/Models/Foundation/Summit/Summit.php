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
use models\main\File;
use models\main\Member;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DateTimeZone;
use DateTime;
use models\main\Company;

/**
 * @ORM\Entity
 * @ORM\Table(name="Summit")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineSummitRepository")
 * Class Summit
 * @package models\summit
 */
class Summit extends SilverstripeBaseModel
{
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getExternalSummitId()
    {
        return $this->external_summit_id;
    }

    /**
     * @param string $external_summit_id
     */
    public function setExternalSummitId($external_summit_id)
    {
        $this->external_summit_id = $external_summit_id;
    }

    /**
     * @return mixed
     */
    public function getScheduleDefaultStartDate()
    {
        return $this->schedule_default_start_date;
    }

    /**
     * @param mixed $schedule_default_start_date
     */
    public function setScheduleDefaultStartDate($schedule_default_start_date)
    {
        $this->schedule_default_start_date = $schedule_default_start_date;
    }

    /**
     * @return mixed
     */
    public function getBeginDate()
    {
        return $this->begin_date;
    }

    /**
     * @param mixed $begin_date
     */
    public function setBeginDate($begin_date)
    {
        $this->begin_date = $begin_date;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param mixed $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getStartShowingVenuesDate()
    {
        return $this->start_showing_venues_date;
    }

    /**
     * @param mixed $start_showing_venues_date
     */
    public function setStartShowingVenuesDate($start_showing_venues_date)
    {
        $this->start_showing_venues_date = $start_showing_venues_date;
    }

    /**
     * @ORM\Column(name="Title", type="string")
     */
    private $name;

    /**
     * @ORM\Column(name="SummitBeginDate", type="datetime")
     * @var \DateTime
     */
    private $begin_date;

    /**
     * @ORM\Column(name="SummitEndDate", type="datetime")
     * @var \DateTime
     */
    private $end_date;

    /**
     * @ORM\Column(name="Active", type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(name="ExternalEventId", type="string")
     * @var string
     */
    private $external_summit_id;

    /**
     * @ORM\Column(name="ScheduleDefaultStartDate", type="date")
     * @var \DateTime
     */
    private $schedule_default_start_date;

    /**
     * @return string
     */
    public function getSummitExternalId(){ return $this->external_summit_id; }

    public function isActive(){
        return $this->active;
    }

    /**
     * @ORM\Column(name="StartShowingVenuesDate", type="datetime")
     */
    private $start_showing_venues_date;

    /**
     * @ORM\Column(name="TimeZone", type="string")
     */
    private $time_zone_id;

    /**
     * @return mixed
     */
    public function getTimeZoneId()
    {
        return $this->time_zone_id;
    }

    /**
     * @param mixed $time_zone_id
     */
    public function setTimeZoneId($time_zone_id)
    {
        $this->time_zone_id = $time_zone_id;
    }

    // ...
    /**
     * @ORM\OneToMany(targetEntity="SummitAbstractLocation", mappedBy="summit", cascade={"persist"})
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="SummitEvent", mappedBy="summit", cascade={"persist"})
     */
    private $events;

    /**
     * Summit constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->locations               = new ArrayCollection();
        $this->events                  = new ArrayCollection();
        $this->event_types             = new ArrayCollection();
        $this->summit_types            = new ArrayCollection();
        $this->ticket_types            = new ArrayCollection();
        $this->presentation_categories = new ArrayCollection();
        $this->category_groups         = new ArrayCollection();
        $this->attendees               = new ArrayCollection();
        $this->entity_events           = new ArrayCollection();
    }

    /**
     * @param DateTime $value
     * @return null|DateTime
     */
    public function convertDateFromTimeZone2UTC(DateTime $value)
    {
        $time_zone_id   = $this->time_zone_id;
        if(empty($time_zone_id)) return $value;
        $time_zone_list = timezone_identifiers_list();

        if(isset($time_zone_list[$time_zone_id]) && !empty($value))
        {
            $utc_timezone      = new DateTimeZone("UTC");
            $time_zone_name    = $time_zone_list[$time_zone_id];
            $summit_time_zone  = new DateTimeZone($time_zone_name);
            $local_date        = $value->setTimezone($summit_time_zone);
            return $local_date->setTimezone($utc_timezone);
        }
        return null;
    }

    /**
     * @param DateTime $value
     * @return null|DateTime
     */
    public function convertDateFromUTC2TimeZone(DateTime $value)
    {
        $time_zone_id   = $this->time_zone_id;
        if(empty($time_zone_id)) return $value;
        $time_zone_list = timezone_identifiers_list();

        if(isset($time_zone_list[$time_zone_id]) && !empty($value))
        {
            $utc_timezone     = new DateTimeZone("UTC");
            $time_zone_name   = $time_zone_list[$time_zone_id];
            $summit_time_zone = new DateTimeZone($time_zone_name);
            $utc_date         = $value->setTimezone($utc_timezone);

            return $utc_date->setTimezone($summit_time_zone);
        }
        return null;
    }

    /**
     * @return DateTime
     */
    public function getLocalBeginDate()
    {
        return $this->convertDateFromUTC2TimeZone($this->begin_date);
    }

    /**
     * @return DateTime
     */
    public function getLocalEndDate()
    {
        return $this->convertDateFromUTC2TimeZone($this->end_date);
    }

    /**
     * @param SummitAbstractLocation $location
     */
    public function addLocation(SummitAbstractLocation $location){
        $this->locations->add($location);
        $location->setSummit($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @return SummitVenue[]
     */
    public function getVenues(){
        return $this->locations->filter(function($e){
            return $e instanceof SummitVenue;
        });
    }

    /**
     * @return ArrayCollection
     */
    public function getEvents(){
        return $this->events;
    }

    /**
     * @param SummitEvent $event
     */
    public function addEvent(SummitEvent $event){
        $this->events->add($event);
        $event->setSummit($this);
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File", fetch="EAGER")
     * @ORM\JoinColumn(name="LogoID", referencedColumnName="ID")
     * @var File
     */
    private $logo;

    /**
     * @return File
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @return bool
     */
    public function hasLogo(){
        return $this->getLogoId() > 0;
    }

    /**
     * @return int
     */
    public function getLogoId(){
        try{
            return !is_null($this->logo)?$this->logo->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param int $location_id
     * @return SummitAbstractLocation
     */
    public function getLocation($location_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($location_id)));
        $location = $this->locations->matching($criteria)->first();
        return $location === false ? null:$location;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventType", mappedBy="summit", cascade={"persist"})
     */
    private $event_types;

    /**
     * @return SummitEventType[]
     */
    public function getEventTypes()
    {
        return $this->event_types;
    }

    /**
     * @param int $event_type_id
     * @return SummitEventType
     */
    public function getEventType($event_type_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($event_type_id)));
        $event_type = $this->event_types->matching($criteria)->first();
        return $event_type === false ? null:$event_type;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitType", mappedBy="summit", cascade={"persist"})
     */
    private $summit_types;

    /**
     * @return SummitType[]
     */
    public function getSummitTypes()
    {
        return $this->summit_types;
    }

    /**
     * @param int $summit_type_id
     * @return SummitType
     */
    public function getSummitType($summit_type_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($summit_type_id)));
        $summit_type = $this->summit_types->matching($criteria)->first();
        return $summit_type === false ? null:$summit_type;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitTicketType", mappedBy="summit", cascade={"persist"})
     */
    private $ticket_types;

    /**
     * @return SummitTicketType[]
     */
    public function getTicketTypes()
    {
        return $this->ticket_types;
    }

    /**
     * @param int $ticket_type_id
     * @return SummitTicketType|null
     */
    public function getTicketType($ticket_type_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($ticket_type_id)));
        $ticket_type  = $this->ticket_types->matching($criteria)->first();
        return $ticket_type === false ? null:$ticket_type;
    }

    /**
     * @param string $ticket_type_external_id
     * @return SummitTicketType|null
     */
    public function getTicketTypeByExternalId($ticket_type_external_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('external_id', $ticket_type_external_id));
        $ticket_type  = $this->ticket_types->matching($criteria)->first();
        return $ticket_type === false ? null:$ticket_type;
    }

    /**
     * @param int $event_id
     * @return null|SummitEvent
     */
    public function getScheduleEvent($event_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('published', 1));
        $criteria->andWhere(Criteria::expr()->eq('id', intval($event_id)));
        $event  = $this->events->matching($criteria)->first();
        return $event === false ? null:$event;
    }

    /**
     * @param int $event_id
     * @return bool
     */
    public function isEventOnSchedule($event_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('published', 1));
        $criteria->andWhere(Criteria::expr()->eq('id', intval($event_id)));
        return $this->events->matching($criteria)->count() > 0;
    }

    public function getScheduleEvents(){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('published', 1));
        $criteria->orderBy(["start_date" => Criteria::ASC, "end_date" => Criteria::ASC]);
        return $this->events->matching($criteria);
    }

    public function getPresentations(){
       $query = $this->createQuery("SELECT p from models\summit\Presentation p JOIN p.summit s WHERE s.id = :summit_id");
       return $query->setParameter('summit_id', $this->getIdentifier())->getResult();
    }
    /**
     * @param int $event_id
     * @return null|SummitEvent
     */
    public function getEvent($event_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($event_id)));
        $event  = $this->events->matching($criteria)->first();
        return $event === false ? null:$event;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\PresentationCategory", mappedBy="summit", cascade={"persist"})
     * @var PresentationCategory[]
     */
    private $presentation_categories;

    /**
     * @return PresentationCategory[]
     */
    public function getPresentationCategories()
    {
        return $this->presentation_categories;
    }

    /**
     * @param int $category_id
     * @return PresentationCategory
     */
    public function getPresentationCategory($category_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($category_id)));
        $category  = $this->presentation_categories->matching($criteria)->first();
        return $category === false ? null:$category;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\PresentationCategoryGroup", mappedBy="summit", cascade={"persist"})
     * @var PresentationCategoryGroup[]
     */
    private $category_groups;

    /**
     * @return PresentationCategoryGroup[]
     */
    public function getCategoryGroups()
    {
        return $this->category_groups;
    }

    /**
     * @param int $group_id
     * @return null|PresentationCategoryGroup
     */
    public function getCategoryGroup($group_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($group_id)));
        $group  = $this->category_groups->matching($criteria)->first();
        return $group === false ? null:$group;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitAttendee", mappedBy="summit", cascade={"persist"})
     * @var SummitAttendee[]
     */
    private $attendees;

    /**
     * @param int $member_id
     * @return SummitAttendee
     */
    public function getAttendeeByMemberId($member_id)
    {
        $builder = $this->createQueryBuilder();
        $members = $builder
            ->select('a')
            ->from('models\summit\SummitAttendee','a')
            ->join('a.member','m')
            ->join('a.summit','s')
            ->where('s.id = :summit_id and m.id = :member_id')
            ->setParameter('summit_id', $this->getId())
            ->setParameter('member_id',  intval($member_id))
            ->getQuery()->getResult();
        return count($members) > 0 ? $members[0] : null;
    }

    /**
     * @param Member $member
     * @return SummitAttendee|null
     */
    public function getAttendeeByMember(Member $member){
        return $this->getAttendeeByMemberId($member->getId());
    }

    /**
     * @param int $attendee_id
     * @return SummitAttendee
     */
    public function getAttendeeById($attendee_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($attendee_id)));
        $attendee = $this->attendees->matching($criteria)->first();
        return $attendee === false ? null:$attendee;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEntityEvent", mappedBy="summit", cascade={"persist"})
     * @var SummitEntityEvent[]
     */
    private $entity_events;

     /**
     * @param SummitEvent $summit_event
     * @return bool
     */
    public function isEventInsideSummitDuration(SummitEvent $summit_event)
    {
        $event_start_date  = $summit_event->getLocalStartDate();
        $event_end_date    = $summit_event->getLocalEndDate();
        $summit_start_date = $this->getLocalBeginDate();
        $summit_end_date   = $this->getLocalEndDate();

        return  $event_start_date >= $summit_start_date && $event_start_date <= $summit_end_date &&
        $event_end_date <= $summit_end_date && $event_end_date >= $event_start_date;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildModeratorsQuery(){
         return $this->createQueryBuilder()
         ->select('distinct ps')
         ->from('models\summit\PresentationSpeaker','ps')
         ->join('ps.presentations','p')
         ->join('p.summit','s')
         ->join('p.moderator','m')
         ->where('s.id = :summit_id and p.published = 1 and m.id = ps.id')
         ->setParameter('summit_id', $this->getId());
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildSpeakersQuery(){
        return $this->createQueryBuilder()
            ->select('distinct ps')
            ->from('models\summit\PresentationSpeaker','ps')
            ->join('ps.presentations','p')
            ->join('p.summit','s')
            ->where("s.id = :summit_id and p.published = 1")
            ->setParameter('summit_id', $this->getId());
    }

    /**
     * @return PresentationSpeaker[]
     */
    public function getSpeakers(){
        // moderators
        $moderators = $this->buildModeratorsQuery()->getQuery()->getResult();
        // get moderators ids to exclude from speakers
        $moderators_ids = array();
        foreach($moderators as $m){
            $moderators_ids[] = $m->getId();
        }

        // speakers
        $sbuilder = $this->buildSpeakersQuery();

        if(count($moderators_ids) > 0){
            $moderators_ids = implode(', ',$moderators_ids);
            $sbuilder = $sbuilder->andWhere("ps.id not in ({$moderators_ids})");
        }

        $speakers = $sbuilder->getQuery()->getResult();

        return array_merge($speakers, $moderators);
    }

    /**
     * @param Member $member
     * @return PresentationSpeaker|null
     */
    public function getSpeakerByMember(Member $member){
        return $this->getSpeakerByMemberId($member->getId());
    }

    /**
     * @param int $member_id
     * @return PresentationSpeaker|null
     */
    public function getSpeakerByMemberId($member_id){
        // moderators
        $moderator = $this->buildModeratorsQuery()
            ->join('ps.member','mb')
            ->andWhere('mb.id = :member_id')
            ->setParameter('member_id', $member_id)
            ->getQuery()->getOneOrNullResult();

        if(!is_null($moderator)) return $moderator;

        // speakers
        $speaker = $this->buildSpeakersQuery()
            ->join('ps.member','mb')
            ->andWhere('mb.id = :member_id')
            ->setParameter('member_id', $member_id)
            ->getQuery()->getOneOrNullResult();

        if(!is_null($speaker)) return $speaker;;

        return null;
    }

    /**
     * @param int $speaker_id
     * @return PresentationSpeaker|null
     */
    public function getSpeaker($speaker_id){
        // moderators
        $moderator = $this->buildModeratorsQuery()
            ->andWhere('ps.id = :speaker_id')
            ->setParameter('speaker_id', $speaker_id)
            ->getQuery()->getOneOrNullResult();

        if(!is_null($moderator)) return $moderator;

        // speakers
        $speaker = $this->buildSpeakersQuery()
            ->andWhere('ps.id = :speaker_id')
            ->setParameter('speaker_id', $speaker_id)
            ->getQuery()->getOneOrNullResult();

        if(!is_null($speaker)) return $speaker;;

        return null;
    }

    /**
     * @return Company[]
     */
    public function getSponsors(){
        $builder = $this->createQueryBuilder();
        return $builder
            ->select('distinct c')
            ->from('models\main\Company','c')
            ->join('c.sponsorships','sp')
            ->join('sp.summit','s')
            ->where('s.id = :summit_id and sp.published = 1')
            ->setParameter('summit_id', $this->getId())->getQuery()->getResult();
    }

    /**
     * @return string
     */
    public function getMainPage(){
        try {
            $sql = <<<SQL
SELECT URLSegment FROM SiteTree
INNER JOIN
SummitPage ON SummitPage.ID = SiteTree.ID 
WHERE SummitID = :summit_id AND ClassName = 'SummitStaticAboutPage';
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : '';
        }
        catch (\Exception $ex){

        }
        return '';
    }

    /**
     * @return string
     */
    public function getSchedulePage(){
        try{
            $sql = <<<SQL
    SELECT URLSegment FROM SiteTree
    INNER JOIN
    SummitPage ON SummitPage.ID = SiteTree.ID 
    WHERE SummitID = :summit_id AND ClassName = 'SummitAppSchedPage';
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : '';
        }
        catch (\Exception $ex){

        }
        return '';
    }

}