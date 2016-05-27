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
use Doctrine\ORM\Query;
use models\main\File;
use models\utils\SilverstripeBaseModel;
use utils\Filter;
use utils\Order;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DateTimeZone;
use DateTime;

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
     */
    private $begin_date;

    /**
     * @ORM\Column(name="SummitEndDate", type="datetime")
     */
    private $end_date;

    /**
     * @ORM\Column(name="Active", type="boolean")
     */
    private $active;

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
     * @param int $location_id
     * @return SummitAbstractLocation
     */
    public function getLocation($location_id)
    {
        return $this->locations->get($location_id);
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
        return $this->event_types->get($event_type_id);
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
        return $this->summit_types->get($summit_type_id);
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
     * @param int $event_id
     * @return null|SummitEvent
     */
    public function getScheduleEvent($event_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('published', 1));
        $criteria->andWhere(Criteria::expr()->eq('id', intval($event_id)));
        return $this->events->matching($criteria)->first();
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
        return $this->events->matching($criteria)->first();
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
        return $this->category_groups->get($group_id);
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitAttendee", mappedBy="summit", cascade={"persist"})
     * @var SummitAttendee[]
     */
    private $attendees;

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @param Order|null $order
     * @return array
     */
    public function attendees($page = 1, $per_page = 100, Filter $filter = null, Order $order = null)
    {
        $rel = $this->hasMany('models\summit\SummitAttendee', 'SummitID', 'ID')->join('Member', 'Member.ID', '=', 'SummitAttendee.MemberID');

        if(!is_null($filter))
        {
            $filter->apply2Relation($rel, array
            (
                'first_name' => 'Member.FirstName',
                'last_name'  => 'Member.Surname',
                'email'      => 'Member.Email',
            ));
        }

        if(!is_null($order))
        {
            $order->apply2Relation($rel, array
            (
                'first_name' => 'Member.FirstName',
                'last_name'  => 'Member.Surname',
            ));
        }

        $pagination_result = $rel->paginate($per_page);
        $total             = $pagination_result->total();
        $items             = $pagination_result->items();
        $per_page          = $pagination_result->perPage();
        $current_page      = $pagination_result->currentPage();
        $last_page         = $pagination_result->lastPage();

        return array ($total,$per_page, $current_page, $last_page, $items);
    }


    /**
     * @param int $member_id
     * @return SummitAttendee
     */
    public function getAttendeeByMemberId($member_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('member.id', intval($member_id)));
        return $this->attendees->matching()->first();
    }

    /**
     * @param int $attendee_id
     * @return SummitAttendee
     */
    public function getAttendeeById($attendee_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($attendee_id)));
        return $this->attendees->matching()->first();
    }


    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEntityEvent", mappedBy="summit", cascade={"persist"})
     * @var SummitEntityEvent[]
     */
    private $entity_events;

    /**
     * @param int|null $member_id
     * @param int|null $from_id
     * @param \DateTime|null $from_date
     * @param int $limit
     * @return SummitEntityEvent[]
     */
    public function getEntityEvents($member_id = null, $from_id = null, \DateTime $from_date = null, $limit = 25)
    {
        $filters = '';
        if(!is_null($from_id))
        {
            $filters .= " AND SummitEntityEvent.ID > {$from_id} ";
        }
        if(!is_null($from_date))
        {
            $str_date = $from_date->format("Y-m-d H:i:s");
            $filters .= " AND SummitEntityEvent.Created >= '{$str_date}' ";
        }

        $query = <<<SQL
SELECT * FROM
(
	SELECT * FROM SummitEntityEvent
	WHERE
	(
		(EntityClassName <> 'MySchedule' AND EntityClassName <> 'SummitAttendee')
		-- GLOBAL TRUNCATE
		OR (EntityClassName = 'WipeData' AND EntityID = 0)
	)
	AND SummitID = {$this->ID}
	{$filters}
	LIMIT {$limit}
)
AS GLOBAL_EVENTS
SQL;

        if(!is_null($member_id)){
            $query .= <<<SQL
 UNION
SELECT * FROM
(
	SELECT * FROM SummitEntityEvent
	WHERE
	(
		EntityClassName = 'MySchedule'
		AND OwnerID = {$member_id}
	)
	AND SummitID = {$this->ID}
	{$filters}
	LIMIT {$limit}
)
AS MY_SCHEDULE
UNION
SELECT * FROM
(
	SELECT * FROM SummitEntityEvent
	WHERE
	(
		EntityClassName = 'WipeData' AND EntityID = {$member_id}
	)
	AND SummitID = {$this->ID}
	{$filters}
	LIMIT {$limit}
) AS USER_WIPE_DATA
SQL;
        }

        $query .= <<<SQL
 ORDER BY Created ASC LIMIT {$limit};
SQL;

        $rows = DB::connection('ss')->select($query);
        $items = array();
        foreach($rows as $row)
        {
            $instance = new SummitEntityEvent();
            $instance->setRawAttributes((array)$row, true);
            array_push($items, $instance);
        }
        return $items;
    }

    /**
     * @return int
     */
    public function getLastEntityEventId(){
        $query = <<<SQL
SELECT ID FROM SummitEntityEvent WHERE SummitID = {$this->ID} ORDER BY ID DESC LIMIT 1;
SQL;

        $last_id     = DB::connection('ss')->select($query);
        $last_id     = intval($last_id[0]->ID);
        return $last_id;
    }

    /**
     * @param SummitEvent $summit_event
     * @return bool
     */
    public function isEventInsideSummitDuration(SummitEvent $summit_event)
    {
        $event_start_date  = $summit_event->getLocalBeginDate();
        $event_end_date    = $summit_event->getLocalEndDate();
        $summit_start_date = $this->getLocalBeginDate();
        $summit_end_date   = $this->getLocalEndDate();

        return  $event_start_date >= $summit_start_date && $event_start_date <= $summit_end_date &&
        $event_end_date <= $summit_end_date && $event_end_date >= $event_start_date;
    }


}