<?php namespace models\main;

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
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use models\exceptions\ValidationException;
use models\summit\CalendarSync\CalendarSyncInfo;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\RSVP;
use models\summit\Summit;
use models\summit\SummitEvent;
use models\summit\SummitEventFeedback;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Member")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineMemberRepository")
 * Class Member
 * @package models\main
 */
class Member extends SilverstripeBaseModel
{

    /**
     * Member constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->feedback           = new ArrayCollection();
        $this->groups             = new ArrayCollection();
        $this->affiliations       = new ArrayCollection();
        $this->team_memberships   = new ArrayCollection();
        $this->favorites          = new ArrayCollection();
        $this->schedule           = new ArrayCollection();
        $this->rsvp               = new ArrayCollection();
        $this->calendars_sync     = new ArrayCollection();
        $this->schedule_sync_info = new ArrayCollection();
    }

    /**
     * @ORM\OneToMany(targetEntity="SummitMemberSchedule", mappedBy="member", cascade={"persist"}, orphanRemoval=true)
     * @var SummitMemberSchedule[]
     */
    private $schedule;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\CalendarSync\ScheduleCalendarSyncInfo", mappedBy="member", cascade={"persist"}, orphanRemoval=true)
     * @var ScheduleCalendarSyncInfo[]
     */
    private $schedule_sync_info;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\CalendarSync\CalendarSyncInfo", mappedBy="owner", cascade={"persist"}, orphanRemoval=true)
     * @var CalendarSyncInfo[]
     */
    private $calendars_sync;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\RSVP", mappedBy="owner", cascade={"persist"})
     * @var RSVP[]
     */
    protected $rsvp;

    /**
     * @return Affiliation[]
     */
    public function getAffiliations()
    {
        return $this->affiliations;
    }

    /**
     * @return Affiliation[]
     */
    public function getCurrentAffiliations()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("is_current", true))
            ->andWhere(Criteria::expr()->eq("end_date", null))
            ->orderBy([
                "start_date" => Criteria::ASC,
            ]);

        return $this->affiliations->matching($criteria);
    }

    /**
     * @return Affiliation[]
     */
    public function getAllAffiliations()
    {
        $criteria = Criteria::create()
            ->orderBy([
                "start_date" => Criteria::ASC,
                "end_date"   => Criteria::ASC,
            ]);
        return $this->affiliations->matching($criteria);
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return ChatTeamMember[]
     */
    public function getTeamMemberships()
    {
        return $this->team_memberships;
    }

    /**
     * @param ChatTeamMember[] $team_memberships
     */
    public function setTeamMemberships($team_memberships)
    {
        $this->team_memberships = $team_memberships;
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Group", inversedBy="members")
     * @ORM\JoinTable(name="Group_Members",
     *      joinColumns={@ORM\JoinColumn(name="MemberID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="GroupID", referencedColumnName="ID")}
     *      )
     * @var Group[]
     */
    private $groups;


    /**
     * @ORM\OneToMany(targetEntity="ChatTeamMember", mappedBy="member", cascade={"persist"}, orphanRemoval=true)
     * @var ChatTeamMember[]
     */
    private $team_memberships;

    /**
     * @return SummitEvent[]
     */
    public function getFavoritesSummitEvents()
    {
        return $this->favorites;
    }

    /**
     * @param SummitMemberFavorite[] $favorites
     */
    public function setFavoritesSummitEvents($favorites)
    {
        $this->favorites = $favorites;
    }

    /**
     * @ORM\OneToMany(targetEntity="SummitMemberFavorite", mappedBy="member", cascade={"persist"}, orphanRemoval=true)
     * @var SummitMemberFavorite[]
     */
    private $favorites;

    /**
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @return string
     */
    public function getLinkedInProfile()
    {
        return $this->linked_in_profile;
    }

    /**
     * @return string
     */
    public function getIrcHandle()
    {
        return $this->irc_handle;
    }

    /**
     * @return string
     */
    public function getTwitterHandle()
    {
        return $this->twitter_handle;
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File")
     * @ORM\JoinColumn(name="PhotoID", referencedColumnName="ID")
     * @var File
     */
    private $photo;

    /**
     * @ORM\Column(name="FirstName", type="string")
     * @var string
     */
    private $first_name;

    /**
     * @ORM\Column(name="Bio", type="string")
     * @var string
     */
    private $bio;

    /**
     * @ORM\Column(name="State", type="string")
     * @var string
     */
    private $state;

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getSecondEmail()
    {
        return $this->second_email;
    }

    /**
     * @param string $second_email
     */
    public function setSecondEmail($second_email)
    {
        $this->second_email = $second_email;
    }

    /**
     * @return string
     */
    public function getThirdEmail()
    {
        return $this->third_email;
    }

    /**
     * @param string $third_email
     */
    public function setThirdEmail($third_email)
    {
        $this->third_email = $third_email;
    }

    /**
     * @ORM\Column(name="Country", type="string")
     * @var string
     */
    private $country;

    /**
     * @ORM\Column(name="Email", type="string")
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="SecondEmail", type="string")
     * @var string
     */
    private $second_email;

    /**
     * @ORM\Column(name="ThirdEmail", type="string")
     * @var string
     */
    private $third_email;

    /**
     * @ORM\Column(name="EmailVerified", type="boolean")
     * @var bool
     */
    private $email_verified;

    /**
     * @ORM\Column(name="EmailVerifiedDate", type="datetime")
     * @var \DateTime
     */
    private $email_verified_date;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        return $this->email_verified;
    }

    /**
     * @return bool
     */
    public function getEmailVerified()
    {
        return $this->email_verified;
    }

    /**
     * @param bool $email_verified
     */
    public function setEmailVerified($email_verified)
    {
        $this->email_verified = $email_verified;
    }

    /**
     * @return \DateTime
     */
    public function getEmailVerifiedDate()
    {
        return $this->email_verified_date;
    }

    /**
     * @param \DateTime $email_verified_date
     */
    public function setEmailVerifiedDate($email_verified_date)
    {
        $this->email_verified_date = $email_verified_date;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @ORM\Column(name="Active", type="boolean")
     * @var bool
     */
    private $active;

    /**
     * @ORM\Column(name="LinkedInProfile", type="string")
     * @var string
     */
    private $linked_in_profile;

    /**
     * @ORM\Column(name="IRCHandle", type="string")
     * @var string
     */
    private $irc_handle;

    /**
     * @ORM\Column(name="TwitterName", type="string")
     * @var string
     */
    private $twitter_handle;

    /**
     * @ORM\Column(name="Gender", type="string")
     * @var string
     */
    private $gender;

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @ORM\Column(name="Surname", type="string")
     * @var string
     */
    private $last_name;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventFeedback", mappedBy="owner", cascade={"persist"})
     * @var SummitEventFeedback[]
     */
    private $feedback;


    /**
     * @ORM\OneToMany(targetEntity="Affiliation", mappedBy="owner", cascade={"persist"})
     */
    private $affiliations;

    /**
     * @return File
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param File $photo
     */
    public function setPhoto(File $photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return SummitEventFeedback[]
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param Summit $summit
     * @return SummitEventFeedback[]
     */
    public function getFeedbackBySummit(Summit $summit)
    {
        return $this->createQueryBuilder()
            ->select('distinct f')
            ->from('models\summit\SummitEventFeedback', 'f')
            ->join('f.event', 'e')
            ->join('f.owner', 'o')
            ->join('e.summit', 's')
            ->where('s.id = :summit_id and o.id = :owner_id and e.published = 1')
            ->setParameter('summit_id', $summit->getId())
            ->setParameter('owner_id', $this->getId())
            ->getQuery()->getResult();
    }

    /**
     * @param SummitEvent $event
     * @return SummitEventFeedback[]
     */
    public function getFeedbackByEvent(SummitEvent $event)
    {
        return $this->createQueryBuilder()
            ->select('distinct f')
            ->from('models\summit\SummitEventFeedback', 'f')
            ->join('f.event', 'e')
            ->join('f.owner', 'o')
            ->join('e.summit', 's')
            ->where('e.id = :event_id and o.id = :owner_id')
            ->setParameter('event_id', $event->getId())
            ->setParameter('owner_id', $this->getId())
            ->getQuery()->getResult();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {

        $admin_group = $this->groups->filter(function ($entity) {
            return $entity->getCode() == Group::AdminGroupCode;
        });

        return !is_null($admin_group) && $admin_group != false && $admin_group->count() > 0;
    }

    /**
     * @return int[]
     */
    public function getGroupsIds()
    {
        $ids = [];
        foreach ($this->getGroups() as $g) {
            $ids[] = intval($g->getId());
        }
        return $ids;
    }

    /**
     * @return string[]
     */
    public function getGroupsCodes()
    {
        $codes = [];
        foreach ($this->getGroups() as $g) {
            $codes[] = $g->getCode();
        }
        return $codes;
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     */
    public function addFavoriteSummitEvent(SummitEvent $event)
    {
        if ($this->isOnFavorite($event))
            throw new ValidationException
            (
                sprintf('Event %s already belongs to member %s favorites.', $event->getId(), $this->getId())
            );
        if (!$event->isPublished())
            throw new ValidationException
            (
                sprintf('Event %s is not published', $event->getId())
            );

        $favorite = new SummitMemberFavorite();

        $favorite->setMember($this);
        $favorite->setEvent($event);
        $this->favorites->add($favorite);
    }

    /**
     * @param SummitEvent $event
     * @return bool
     */
    public function isOnFavorite(SummitEvent $event)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('event', $event));
        return $this->favorites->matching($criteria)->count() > 0;
    }

    /**
     * @param SummitEvent $event
     * @throws ValidationException
     */
    public function removeFavoriteSummitEvent(SummitEvent $event)
    {
        $favorite = $this->getFavoriteByEvent($event);

        if(is_null($favorite))
            throw new ValidationException
            (
                sprintf('Event %s does not belongs to member %s favorite.', $event->getId(), $this->getId())
            );
        $this->schedule->removeElement($favorite);
        $favorite->clearOwner();
    }

    /**
     * @param  Summit $summit
     * @return int[]
     */
    public function getFavoritesEventsIds(Summit $summit)
    {
        $sql = <<<SQL
SELECT SummitEventID 
FROM Member_FavoriteSummitEvents 
INNER JOIN SummitEvent ON SummitEvent.ID = Member_FavoriteSummitEvents.SummitEventID
WHERE MemberID = :member_id AND SummitEvent.Published = 1 AND SummitEvent.SummitID = :summit_id
SQL;

        $stmt = $this->prepareRawSQL($sql);
        $stmt->execute(
            [
                'member_id' => $this->getId(),
                'summit_id' => $summit->getId(),
            ]
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
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
                sprintf('Event %s already belongs to member %s schedule.', $event->getId(), $this->getId())
            );

        if(!$event->isPublished())
            throw new ValidationException
            (
                sprintf('Event %s is not published', $event->getId())
            );

        $schedule = new SummitMemberSchedule();

        $schedule->setMember($this);
        $schedule->setEvent($event);
        $this->schedule->add($schedule);
    }

    /**
     * @param ScheduleCalendarSyncInfo $sync_info
     */
    public function add2ScheduleSyncInfo(ScheduleCalendarSyncInfo $sync_info){
        $sync_info->setMember($this);
        $this->schedule_sync_info->add($sync_info);
    }

    public function removeFromSchedule(SummitEvent $event)
    {
        $schedule = $this->getScheduleByEvent($event);

        if(is_null($schedule))
            throw new ValidationException
            (
                sprintf('Event %s does not belongs to member %s schedule.', $event->getId(), $this->getId())
            );
        $this->schedule->removeElement($schedule);
        $schedule->clearOwner();
    }

    public function removeFromScheduleSyncInfo(ScheduleCalendarSyncInfo $sync_info){
        $this->schedule_sync_info->removeElement($sync_info);
        $sync_info->clearOwner();
    }

    /**
     * @param CalendarSyncInfo $calendar_sync_info
     * @param SummitEvent $event
     * @return bool
     */
    public function isEventSynchronized(CalendarSyncInfo $calendar_sync_info, SummitEvent $event){

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('summit_event', $event));
        $criteria->andWhere(Criteria::expr()->eq('calendar_sync_info', $calendar_sync_info));
        return $this->schedule_sync_info->matching($criteria)->count() > 0;
    }

    /**
     * @param SummitEvent $event
     * @return bool
     */
    public function isOnSchedule(SummitEvent $event)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('event', $event));
        return $this->schedule->matching($criteria)->count() > 0;
    }

    /**
     * @param SummitEvent $event
     * @return null| SummitMemberSchedule
     */
    public function getScheduleByEvent(SummitEvent $event){

        try {
            $query = $this->createQuery("SELECT s from models\main\SummitMemberSchedule s 
        JOIN s.member a 
        JOIN s.event e    
        WHERE a.id = :member_id and e.id = :event_id
        ");
            return $query
                ->setParameter('member_id', $this->getIdentifier())
                ->setParameter('event_id', $event->getIdentifier())
                ->getSingleResult();
        }
        catch(NoResultException $ex1){
            return null;
        }
        catch(NonUniqueResultException $ex2){
            // should never happen
            return null;
        }
    }

    /**
     * @param SummitEvent $event
     * @param CalendarSyncInfo $calendar_sync_info
     * @return ScheduleCalendarSyncInfo|null
     */
    public function getScheduleSyncInfoByEvent(SummitEvent $event, CalendarSyncInfo $calendar_sync_info){
        try {
            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->eq('summit_event', $event));
            $criteria->andWhere(Criteria::expr()->eq('calendar_sync_info', $calendar_sync_info));
            return $this->schedule_sync_info->matching($criteria)->first();
        }
        catch(NoResultException $ex1){
            return null;
        }
        catch(NonUniqueResultException $ex2){
            // should never happen
            return null;
        }
    }

    /**
     * @param SummitEvent $event
     * @return SummitMemberFavorite|null
     */
    public function getFavoriteByEvent(SummitEvent $event){
        try {
            $query = $this->createQuery("SELECT f from models\main\SummitMemberFavorite f 
        JOIN f.member a 
        JOIN f.event e    
        WHERE a.id = :member_id and e.id = :event_id
        ");
            return $query
                ->setParameter('member_id', $this->getIdentifier())
                ->setParameter('event_id', $event->getIdentifier())
                ->getSingleResult();
        }
        catch(NoResultException $ex1){
            return null;
        }
        catch(NonUniqueResultException $ex2){
            // should never happen
            return null;
        }
    }

    /**
     * @param  Summit $summit
     * @return int[]
     */
    public function getScheduledEventsIds(Summit $summit){
        $sql = <<<SQL
SELECT SummitEventID 
FROM Member_Schedule 
INNER JOIN SummitEvent ON SummitEvent.ID = Member_Schedule.SummitEventID
WHERE MemberID = :member_id AND SummitEvent.Published = 1 AND SummitEvent.SummitID = :summit_id
SQL;

        $stmt = $this->prepareRawSQL($sql);
        $stmt->execute(
            [
                'member_id' => $this->getId(),
                'summit_id' => $summit->getId(),
            ]
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param int $event_id
     * @return null|RSVP
     */
    public function getRsvpByEvent($event_id){
        $builder = $this->createQueryBuilder();
        $rsvp = $builder
            ->select('r')
            ->from('models\summit\RSVP','r')
            ->join('r.owner','o')
            ->join('r.event','e')
            ->where('o.id = :owner_id and e.id = :event_id')
            ->setParameter('owner_id', $this->getId())
            ->setParameter('event_id',  intval($event_id))
            ->getQuery()->getResult();

        return count($rsvp) > 0 ? $rsvp[0] : null;
    }

    /**
     * @param Summit $summit
     * @return SummitMemberSchedule[]
     */
    public function getScheduleBySummit(Summit $summit){

        $query = $this->createQuery("SELECT s from models\main\SummitMemberSchedule s
        JOIN s.member m
        JOIN s.event e 
        JOIN e.summit su WHERE su.id = :summit_id and m.id = :member_id ");

        return $query
            ->setParameter('member_id', $this->getId())
            ->setParameter('summit_id', $summit->getId())
            ->getResult();
    }

    /**
     * @param Summit $summit
     * @return SummitMemberFavorite[]
     */
    public function getFavoritesSummitEventsBySummit(Summit $summit)
    {
        $query = $this->createQuery("SELECT f from models\main\SummitMemberFavorite f
        JOIN f.member m
        JOIN f.event e 
        JOIN e.summit su WHERE su.id = :summit_id and m.id = :member_id ");

        return $query
            ->setParameter('member_id', $this->getId())
            ->setParameter('summit_id', $summit->getId())
            ->getResult();
    }

    /**
     * @param Summit $summit
     * @return CalendarSyncInfo[]
     */
    public function getSyncInfoBy(Summit $summit){
        $res = $this->calendars_sync->filter(function($entity) use($summit){
            return $entity->getSummit()->getIdentifier() == $summit->getIdentifier() && !$entity->isRevoked();
        });
        return count($res) > 0 ? $res[0] : null;
    }

    /**
     * @param Summit $summit
     * @return bool
     */
    public function hasSyncInfoFor(Summit $summit){
        return !is_null($this->getSyncInfoBy($summit));
    }

    /**
     * @param CalendarSyncInfo $calendar_sync_info
     */
    public function removeFromCalendarSyncInfo(CalendarSyncInfo $calendar_sync_info){
        $this->calendars_sync->removeElement($calendar_sync_info);
        $calendar_sync_info->clearOwner();
    }
}