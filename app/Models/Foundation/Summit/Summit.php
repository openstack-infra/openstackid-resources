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
use App\Http\Utils\DateUtils;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTemplate;
use App\Models\Foundation\Summit\SelectionPlan;
use App\Models\Foundation\Summit\TrackTagGroup;
use App\Models\Utils\TimeZoneEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Select;
use models\exceptions\ValidationException;
use models\main\Company;
use models\main\File;
use models\main\Member;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitRepository")
 * @ORM\Table(name="Summit")
 * Class Summit
 * @package models\summit
 */
class Summit extends SilverstripeBaseModel
{

    use TimeZoneEntity;
    /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="DateLabel", type="string")
     * @var string
     */
    private $dates_label;

    /**
     * @ORM\Column(name="Link", type="string")
     * @var string
     */
    private $link;

    /**
     * @ORM\Column(name="Slug", type="string")
     * @var string
     */
    private $slug;

    /**
     * @ORM\Column(name="RegistrationLink", type="string")
     * @var string
     */
    private $registration_link;

    /**
     * @ORM\Column(name="MaxSubmissionAllowedPerUser", type="integer")
     * @var int
     */
    private $max_submission_allowed_per_user;

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
     * @ORM\Column(name="RegistrationBeginDate", type="datetime")
     * @var \DateTime
     */
    private $registration_begin_date;

    /**
     * @ORM\Column(name="RegistrationEndDate", type="datetime")
     * @var \DateTime
     */
    private $registration_end_date;

    /**
     * @ORM\Column(name="Active", type="boolean")
     * @var bool
     */
    private $active;

    /**
     * @ORM\Column(name="AvailableOnApi", type="boolean")
     * @var bool
     */
    private $available_on_api;

    /**
     * @ORM\Column(name="ExternalEventId", type="string")
     * @var string
     */
    private $external_summit_id;

    /**
     * @ORM\Column(name="ScheduleDefaultStartDate", type="datetime")
     * @var \DateTime
     */
    private $schedule_default_start_date;

    /**
     * @ORM\ManyToOne(targetEntity="SummitType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="TypeID", referencedColumnName="ID")
     * @var SummitType
     */
    private $type = null;

    /**
     * @ORM\Column(name="StartShowingVenuesDate", type="datetime")
     */
    private $start_showing_venues_date;

    /**
     * @ORM\Column(name="TimeZoneIdentifier", type="string")
     * @var string
     */
    private $time_zone_id;

    /**
     * @ORM\Column(name="SecondaryRegistrationLink", type="string")
     * @var string
     */
    private $secondary_registration_link;

    /**
     * @ORM\Column(name="SecondaryRegistrationBtnText", type="string")
     * @var string
     */
    private $secondary_registration_label;

    /**
     * @ORM\Column(name="CalendarSyncName", type="string")
     * @var string
     */
    private $calendar_sync_name;

    /**
     * @ORM\Column(name="CalendarSyncDescription", type="string")
     * @var string
     */
    private $calendar_sync_desc;

    /**
     * @ORM\OneToMany(targetEntity="SummitAbstractLocation", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="SummitEvent", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\Foundation\Summit\Events\RSVP\RSVPTemplate", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $rsvp_templates;

    /**
     * @ORM\OneToMany(targetEntity="SummitWIFIConnection", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var SummitWIFIConnection[]
     */
    private $wifi_connections;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\Foundation\Summit\TrackTagGroup", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var TrackTagGroup[]
     */
    private $track_tag_groups;

    /**
     * @ORM\OneToMany(targetEntity="SummitRegistrationPromoCode", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var SummitRegistrationPromoCode[]
     */
    private $promo_codes;

    /**
     * @ORM\OneToMany(targetEntity="PresentationSpeakerSummitAssistanceConfirmationRequest", mappedBy="summit", fetch="EXTRA_LAZY")
     * @var PresentationSpeakerSummitAssistanceConfirmationRequest[]
     */
    private $speaker_assistances;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File")
     * @ORM\JoinColumn(name="LogoID", referencedColumnName="ID")
     * @var File
     */
    private $logo;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventType", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $event_types;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\PresentationCategory", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var PresentationCategory[]
     */
    private $presentation_categories;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitAttendee", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var SummitAttendee[]
     */
    private $attendees;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\Foundation\Summit\SelectionPlan", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var SelectionPlan[]
     */
    private $selection_plans;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\PresentationCategoryGroup", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var PresentationCategoryGroup[]
     */
    private $category_groups;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitTicketType", mappedBy="summit", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     */
    private $ticket_types;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory")
     * @ORM\JoinTable(name="Summit_ExcludedCategoriesForAcceptedPresentations",
     *      joinColumns={@ORM\JoinColumn(name="SummitID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")}
     * )
     * @var PresentationCategory[]
     */
    private $excluded_categories_for_accepted_presentations;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory")
     * @ORM\JoinTable(name="Summit_ExcludedCategoriesForAlternatePresentations",
     *      joinColumns={@ORM\JoinColumn(name="SummitID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")}
     * )
     * @var PresentationCategory[]
     */
    private $excluded_categories_for_alternate_presentations;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory")
     * @ORM\JoinTable(name="Summit_ExcludedCategoriesForRejectedPresentations",
     *      joinColumns={@ORM\JoinColumn(name="SummitID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")}
     * )
     * @var PresentationCategory[]
     */
    private $excluded_categories_for_rejected_presentations;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory")
     * @ORM\JoinTable(name="Summit_ExcludedTracksForUploadPresentationSlideDeck",
     *      joinColumns={@ORM\JoinColumn(name="SummitID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")}
     * )
     * @var PresentationCategory[]
     */
    private $excluded_categories_for_upload_slide_decks;

    /**
     * @return string
     */
    public function getDatesLabel()
    {
        return $this->dates_label;
    }

    /**
     * @param string $dates_label
     */
    public function setDatesLabel($dates_label)
    {
        $this->dates_label = $dates_label;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getWifiConnections()
    {
        return $this->wifi_connections;
    }

    /**
     * @param mixed $wifi_connections
     */
    public function setWifiConnections($wifi_connections)
    {
        $this->wifi_connections = $wifi_connections;
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
     * @return \DateTime
     */
    public function getScheduleDefaultStartDate()
    {
        return $this->schedule_default_start_date;
    }

    /**
     * @param \DateTime $schedule_default_start_date
     */
    public function setScheduleDefaultStartDate($schedule_default_start_date)
    {
        $this->schedule_default_start_date = $this->convertDateFromTimeZone2UTC($schedule_default_start_date);
    }

    public function clearScheduleDefaultStartDate(){
        $this->schedule_default_start_date = null;
    }

    /**
     * @return mixed
     */
    public function getBeginDate()
    {
        return $this->begin_date;
    }

    /**
     * @param \DateTime $begin_date
     */
    public function setBeginDate($begin_date)
    {
       $this->begin_date = $this->convertDateFromTimeZone2UTC($begin_date);
    }

    /**
     * @return $this
     */
    public function clearBeginEndDates(){
        $this->begin_date = $this->end_date = null;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param \DateTime $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $this->convertDateFromTimeZone2UTC($end_date);
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
     * @return \DateTime
     */
    public function getStartShowingVenuesDate()
    {
        return $this->start_showing_venues_date;
    }

    /**
     * @param \DateTime $start_showing_venues_date
     */
    public function setStartShowingVenuesDate($start_showing_venues_date)
    {
        $this->start_showing_venues_date = $this->convertDateFromTimeZone2UTC($start_showing_venues_date);
    }

    public function clearStartShowingVenuesDate(){
        $this->start_showing_venues_date = null;
    }

    /**
     * @return boolean
     */
    public function isAvailableOnApi()
    {
        return $this->available_on_api;
    }

    /**
     * @param boolean $available_on_api
     */
    public function setAvailableOnApi($available_on_api)
    {
        $this->available_on_api = $available_on_api;
    }

    /**
     * @return SummitType
     */
    public function getType()
    {
        try {
            return $this->type;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * @param SummitType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        try {
            return !is_null($this->type) ? $this->type->getId() : 0;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasType()
    {
        return $this->getTypeId() > 0;
    }

    /**
     * @return string
     */
    public function getSummitExternalId()
    {
        return $this->external_summit_id;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }


    const DefaultMaxSubmissionAllowedPerUser = 3;
    /**
     * Summit constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // default values
        $this->active  = false;
        $this->available_on_api = false;
        $this->max_submission_allowed_per_user = self::DefaultMaxSubmissionAllowedPerUser;

        $this->locations = new ArrayCollection;
        $this->events = new ArrayCollection;
        $this->event_types = new ArrayCollection;
        $this->ticket_types = new ArrayCollection;
        $this->presentation_categories = new ArrayCollection;
        $this->category_groups = new ArrayCollection;
        $this->attendees = new ArrayCollection;
        $this->entity_events = new ArrayCollection;
        $this->wifi_connections = new ArrayCollection;
        $this->promo_codes = new ArrayCollection;
        $this->speaker_assistances = new ArrayCollection;
        $this->excluded_categories_for_accepted_presentations = new ArrayCollection;
        $this->excluded_categories_for_alternate_presentations = new ArrayCollection;
        $this->excluded_categories_for_rejected_presentations = new ArrayCollection;
        $this->excluded_categories_for_upload_slide_decks = new ArrayCollection;
        $this->rsvp_templates = new ArrayCollection;
        $this->track_tag_groups = new ArrayCollection;
        $this->notifications = new ArrayCollection;
        $this->selection_plans = new ArrayCollection;
    }

    /**
     * @param int $assistance_id
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest|null
     */
    public function getSpeakerAssistanceById($assistance_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($assistance_id)));
        $speaker_assistance = $this->speaker_assistances->matching($criteria)->first();
        return $speaker_assistance === false ? null : $speaker_assistance;
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
     * @return $this
     */
    public function addLocation(SummitAbstractLocation $location)
    {
        $this->locations->add($location);
        $location->setSummit($this);
        $location->setOrder($this->getLocationMaxOrder() + 1);
        return $this;
    }

    /**
     * @return int
     */
    private function getLocationMaxOrder(){
        $criteria = Criteria::create();
        $criteria->orderBy(['order' => 'DESC']);
        $location = $this->locations->matching($criteria)->first();
        return $location === false ? 0 : $location->getOrder();
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
    public function getVenues()
    {
        return $this->locations->filter(function ($e) {
            return $e instanceof SummitVenue;
        });
    }

    /**
     * @param string $name
     * @return SummitAbstractLocation|null
     */
    public function getLocationByName($name){

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', trim($name)));
        $location = $this->locations->matching($criteria)->first();
        return $location === false ? null : $location;
    }

    /**
     * @return SummitHotel[]
     */
    public function getHotels()
    {
        return $this->locations->filter(function ($e) {
            return $e instanceof SummitHotel;
        });
    }

    /**
     * @return SummitAirport[]
     */
    public function getAirports()
    {
        return $this->locations->filter(function ($e) {
            return $e instanceof SummitAirport;
        });
    }

    /**
     * @return SummitExternalLocation[]
     */
    public function getExternalLocations()
    {
        return $this->locations->filter(function ($e) {
            return $e->getClassName() == 'SummitExternalLocation';
        });
    }

    /**
     * @return ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param SummitEvent $event
     */
    public function addEvent(SummitEvent $event)
    {
        $this->events->add($event);
        $event->setSummit($this);
    }

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
    public function hasLogo()
    {
        return $this->getLogoId() > 0;
    }

    /**
     * @return int
     */
    public function getLogoId()
    {
        try {
            return !is_null($this->logo) ? $this->logo->getId() : 0;
        } catch (\Exception $ex) {
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
        return $location === false ? null : $location;
    }

    /**
     * @param SummitAbstractLocation $location
     * @return bool
     */
    static public function isPrimaryLocation(SummitAbstractLocation $location){
        return ($location instanceof SummitVenue
            || $location instanceof SummitHotel
            || $location instanceof SummitAirport
            || $location instanceof SummitExternalLocation);
    }

    /**
     * @return SummitEventType[]
     */
    public function getEventTypes()
    {
        return $this->event_types;
    }

    /**
     * @param int $event_type_id
     * @return SummitEventType|null
     */
    public function getEventType($event_type_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($event_type_id)));
        $event_type = $this->event_types->matching($criteria)->first();
        return $event_type === false ? null : $event_type;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasEventType($type)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('type', $type));
        return $this->event_types->matching($criteria)->count() > 0;
    }

    /**
     * @param string $type
     * @return SummitEventType|null
     */
    public function getEventTypeByType($type)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('type', $type));
        $event_type = $this->event_types->matching($criteria)->first();
        return $event_type === false ? null : $event_type;
    }

    /**
     * @param int $wifi_connection_id
     * @return SummitWIFIConnection|null
     */
    public function getWifiConnection($wifi_connection_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($wifi_connection_id)));
        $wifi_conn = $this->wifi_connections->matching($criteria)->first();
        return $wifi_conn === false ? null : $wifi_conn;
    }

    /**
     * @return SummitTicketType[]
     */
    public function getTicketTypes()
    {
        return $this->ticket_types;
    }

    /**
     * @param string $ticket_type_external_id
     * @return SummitTicketType|null
     */
    public function getTicketTypeByExternalId($ticket_type_external_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('external_id', $ticket_type_external_id));
        $ticket_type = $this->ticket_types->matching($criteria)->first();
        return $ticket_type === false ? null : $ticket_type;
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
        $event = $this->events->matching($criteria)->first();
        return $event === false ? null : $event;
    }

    /**
     * @param int $event_id
     * @return bool
     */
    public function isEventOnSchedule($event_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('published', 1));
        $criteria->andWhere(Criteria::expr()->eq('id', intval($event_id)));
        return $this->events->matching($criteria)->count() > 0;
    }

    public function getScheduleEvents()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('published', 1));
        $criteria->orderBy(["start_date" => Criteria::ASC, "end_date" => Criteria::ASC]);
        return $this->events->matching($criteria);
    }

    public function getPresentations()
    {
        $query = $this->createQuery("SELECT p from models\summit\Presentation p JOIN p.summit s WHERE s.id = :summit_id");
        return $query->setParameter('summit_id', $this->getIdentifier())->getResult();
    }


    /**
     * @param PresentationSpeaker $speaker
     * @param SelectionPlan $selectionPlan
     * @return array
     */
    public function getModeratedPresentationsBy(PresentationSpeaker $speaker, SelectionPlan $selectionPlan){
        $query = $this->createQuery("SELECT p from models\summit\Presentation p 
        JOIN p.summit s
        JOIN p.moderator m 
        JOIN p.selection_plan sp
        WHERE s.id = :summit_id and m.id = :moderator_id and sp.id = :selection_plan_id");
        return $query
                ->setParameter('summit_id', $this->getIdentifier())
                ->setParameter('moderator_id', $speaker->getIdentifier())
                ->setParameter('selection_plan_id', $selectionPlan->getIdentifier())
                ->getResult();
    }

    /**
     * @param PresentationSpeaker $speaker
     * @param SelectionPlan $selectionPlan
     * @return array
     */
    public function getCreatedPresentations(PresentationSpeaker $speaker, SelectionPlan $selectionPlan){
        $query = $this->createQuery("SELECT p from models\summit\Presentation p 
        JOIN p.summit s
        JOIN p.creator c 
        JOIN p.selection_plan sp
        WHERE s.id = :summit_id and c.id = :creator_id and sp.id = :selection_plan_id");
        return $query
            ->setParameter('summit_id', $this->getIdentifier())
            ->setParameter('creator_id', $speaker->getMemberId())
            ->setParameter('selection_plan_id', $selectionPlan->getIdentifier())
            ->getResult();
    }

    /**
     * @param int $event_id
     * @return null|SummitEvent
     */
    public function getEvent($event_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($event_id)));
        $event = $this->events->matching($criteria)->first();
        return $event === false ? null : $event;
    }


    /**
     * @return PresentationCategory[]
     */
    public function getPresentationCategories()
    {
        return $this->presentation_categories;
    }

    /**
     * @param PresentationCategory $track
     * @return $this
     */
    public function addPresentationCategory(PresentationCategory $track)
    {
        $this->presentation_categories->add($track);
        $track->setSummit($this);
        return $this;
    }

    /**
     * @param int $category_id
     * @return PresentationCategory
     */
    public function getPresentationCategory($category_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($category_id)));
        $category = $this->presentation_categories->matching($criteria)->first();
        return $category === false ? null : $category;
    }

    /**
     * @param string $category_title
     * @return PresentationCategory
     */
    public function getPresentationCategoryByTitle($category_title)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('title', trim($category_title)));
        $category = $this->presentation_categories->matching($criteria)->first();
        return $category === false ? null : $category;
    }

    /**
     * @param string $category_code
     * @return PresentationCategory
     */
    public function getPresentationCategoryByCode($category_code)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('code', trim($category_code)));
        $category = $this->presentation_categories->matching($criteria)->first();
        return $category === false ? null : $category;
    }

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
    public function getCategoryGroupById($group_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($group_id)));
        $group = $this->category_groups->matching($criteria)->first();
        return $group === false ? null : $group;
    }

    /**
     * @param string $name
     * @return null|PresentationCategoryGroup
     */
    public function getCategoryGroupByName($name)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', trim($name)));
        $group = $this->category_groups->matching($criteria)->first();
        return $group === false ? null : $group;
    }

    /**
     * @param PresentationCategoryGroup $track_group
     */
    public function addCategoryGroup(PresentationCategoryGroup $track_group){
        if($this->category_groups->contains($track_group)) return;
        $this->category_groups->add($track_group);
        $track_group->setSummit($this);
    }

    /**
     * @param PresentationCategoryGroup $track_group
     */
    public function removeCategoryGroup(PresentationCategoryGroup $track_group){
        if(!$this->category_groups->contains($track_group)) return;
        $this->category_groups->removeElement($track_group);
        $track_group->clearSummit();
    }

    /**
     * @param int $member_id
     * @return SummitAttendee
     */
    public function getAttendeeByMemberId($member_id)
    {
        $builder = $this->createQueryBuilder();
        $members = $builder
            ->select('a')
            ->from('models\summit\SummitAttendee', 'a')
            ->join('a.member', 'm')
            ->join('a.summit', 's')
            ->where('s.id = :summit_id and m.id = :member_id')
            ->setParameter('summit_id', $this->getId())
            ->setParameter('member_id', intval($member_id))
            ->getQuery()->getResult();
        return count($members) > 0 ? $members[0] : null;
    }

    /**
     * @param Member $member
     * @return SummitAttendee|null
     */
    public function getAttendeeByMember(Member $member)
    {
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
        return $attendee === false ? null : $attendee;
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEntityEvent", mappedBy="summit", cascade={"persist"}, orphanRemoval=true)
     * @var SummitEntityEvent[]
     */
    private $entity_events;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitPushNotification", mappedBy="summit", cascade={"persist"}, orphanRemoval=true)
     * @var SummitPushNotification[]
     */
    private $notifications;

    /**
     * @param SummitEvent $summit_event
     * @return bool
     */
    public function isEventInsideSummitDuration(SummitEvent $summit_event)
    {
        return $this->isTimeFrameInsideSummitDuration($summit_event->getLocalStartDate(), $summit_event->getLocalEndDate());
    }

    /**
     * @param DateTime $start_date
     * @param DateTime $end_date
     * @return bool
     */
    public function isTimeFrameInsideSummitDuration(DateTime $start_date, DateTime $end_date )
    {
        $summit_start_date = $this->getLocalBeginDate();
        $summit_end_date = $this->getLocalEndDate();

        return $start_date >= $summit_start_date && $start_date <= $summit_end_date &&
            $end_date <= $summit_end_date && $end_date >= $start_date;
    }

    /**
     * @param bool $filter_published_events
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildModeratorsQuery($filter_published_events = true)
    {
        $query = $this->createQueryBuilder()
            ->select('distinct ps')
            ->from('models\summit\PresentationSpeaker', 'ps')
            ->join('ps.moderated_presentations', 'p')
            ->join('p.summit', 's')
            ->where("s.id = :summit_id");
        if ($filter_published_events)
            $query = $query->andWhere("p.published = 1");
        return $query->setParameter('summit_id', $this->getId());
    }

    /**
     * @param bool $filter_published_events
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildSpeakersQuery($filter_published_events = true)
    {
        $query = $this->createQueryBuilder()
            ->select('distinct ps')
            ->from('models\summit\PresentationSpeaker', 'ps')
            ->join('ps.presentations', 'p')
            ->join('p.summit', 's')
            ->where("s.id = :summit_id");

        if ($filter_published_events)
            $query = $query->andWhere("p.published = 1");
        return $query->setParameter('summit_id', $this->getId());
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildSpeakerSummitAttendanceQuery()
    {
        return $this->createQueryBuilder()
            ->select('distinct ps')
            ->from('models\summit\PresentationSpeaker', 'ps')
            ->join('ps.summit_assistances', 'a')
            ->join('a.summit', 's')
            ->where("s.id = :summit_id")
            ->setParameter('summit_id', $this->getId());
    }

    /**
     * @return PresentationSpeaker[]
     */
    public function getSpeakers()
    {
        // moderators
        $moderators = $this->buildModeratorsQuery()->getQuery()->getResult();
        // get moderators ids to exclude from speakers
        $moderators_ids = array();
        foreach ($moderators as $m) {
            $moderators_ids[] = $m->getId();
        }

        // speakers
        $sbuilder = $this->buildSpeakersQuery();

        if (count($moderators_ids) > 0) {
            $moderators_ids = implode(', ', $moderators_ids);
            $sbuilder = $sbuilder->andWhere("ps.id not in ({$moderators_ids})");
        }

        $speakers = $sbuilder->getQuery()->getResult();

        return array_merge($speakers, $moderators);
    }

    /**
     * @param Member $member
     * @return PresentationSpeaker|null
     */
    public function getSpeakerByMember(Member $member)
    {
        return $this->getSpeakerByMemberId($member->getId());
    }

    /**`
     * @param int $member_id
     * @param bool $filter_published_events
     * @return PresentationSpeaker|null
     */
    public function getSpeakerByMemberId($member_id, $filter_published_events = true)
    {
        // moderators
        $moderator = $this->buildModeratorsQuery($filter_published_events)
            ->join('ps.member', 'mb')
            ->andWhere('mb.id = :member_id')
            ->setParameter('member_id', $member_id)
            ->getQuery()->getOneOrNullResult();

        if (!is_null($moderator)) return $moderator;

        // speakers
        $speaker = $this->buildSpeakersQuery($filter_published_events)
            ->join('ps.member', 'mb')
            ->andWhere('mb.id = :member_id')
            ->setParameter('member_id', $member_id)
            ->getQuery()->getOneOrNullResult();

        if (!is_null($speaker)) return $speaker;

        // assistance
        $speaker = $this->buildSpeakerSummitAttendanceQuery()
            ->join('ps.member', 'mb')
            ->andWhere('mb.id = :member_id')
            ->setParameter('member_id', $member_id)
            ->getQuery()->getOneOrNullResult();

        if (!is_null($speaker)) return $speaker;

        return null;
    }

    /**
     * @param int $speaker_id
     * @param bool $filter_published_events
     * @return PresentationSpeaker|null
     */
    public function getSpeaker($speaker_id, $filter_published_events = true)
    {
        // moderators
        $moderator = $this->buildModeratorsQuery($filter_published_events)
            ->andWhere('ps.id = :speaker_id')
            ->setParameter('speaker_id', $speaker_id)
            ->getQuery()->getOneOrNullResult();

        if (!is_null($moderator)) return $moderator;

        // speakers
        $speaker = $this->buildSpeakersQuery($filter_published_events)
            ->andWhere('ps.id = :speaker_id')
            ->setParameter('speaker_id', $speaker_id)
            ->getQuery()->getOneOrNullResult();

        if (!is_null($speaker)) return $speaker;

        // attendance
        $speaker = $this->buildSpeakerSummitAttendanceQuery()
            ->andWhere('ps.id = :speaker_id')
            ->setParameter('speaker_id', $speaker_id)
            ->getQuery()->getOneOrNullResult();

        if (!is_null($speaker)) return $speaker;

        return null;
    }

    /**
     * @return Company[]
     */
    public function getSponsors()
    {
        $builder = $this->createQueryBuilder();
        return $builder
            ->select('distinct c')
            ->from('models\main\Company', 'c')
            ->join('c.sponsorships', 'sp')
            ->join('sp.summit', 's')
            ->where('s.id = :summit_id and sp.published = 1')
            ->setParameter('summit_id', $this->getId())->getQuery()->getResult();
    }

    /**
     * @return string
     */
    public function getMainPage()
    {
        try {
            $sql = <<<SQL
SELECT URLSegment FROM SiteTree
INNER JOIN
SummitPage ON SummitPage.ID = SiteTree.ID 
WHERE SummitID = :summit_id AND ClassName IN ('SummitStaticAboutBostonPage', 'SummitStaticAboutPage','SummitNewStaticAboutPage','SummitHighlightsPage', 'SummitAboutPage');
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : '';
        } catch (\Exception $ex) {

        }
        return '';
    }

    /**
     * @return string
     */
    public function getSchedulePage()
    {
        try {
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
        } catch (\Exception $ex) {

        }
        return '';
    }

    /**
     * @param SummitEvent $summit_event
     * @param Member|null $member
     * @return bool
     * @throws ValidationException
     */
    static public function allowToSee(SummitEvent $summit_event, Member $member = null)
    {

        $event_type = $summit_event->getType();

        if (is_null($event_type))
            throw new ValidationException(sprintf("event type is null for event id %s", $summit_event->getId()));

        if (!$event_type->isPrivate()) return true;

        if (is_null($member)) return false;

        if ($member->isAdmin()) return true;

        // i am logged, check if i have permissions
        if ($summit_event instanceof SummitGroupEvent) {

            $member_groups_code = [];
            $event_groups_code  = [];

            foreach ($member->getGroups() as $member_group) {
                $member_groups_code[] = $member_group->getCode();
            }

            foreach ($summit_event->getGroups() as $event_group) {
                $event_groups_code[] = $event_group->getCode();
            }

            return count(array_intersect($event_groups_code, $member_groups_code)) > 0;
        }
        return true;
    }

    /**
     * @param Member $member
     * @return SummitGroupEvent[]
     */
    public function getGroupEventsFor(Member $member)
    {
        $builder = $this->createQueryBuilder()
            ->select('distinct eg')
            ->from('models\summit\SummitGroupEvent', 'eg')
            ->join('eg.groups', 'g')
            ->join('eg.summit', 's')
            ->where("s.id = :summit_id and eg.published = 1")
            ->setParameter('summit_id', $this->getId());

        if (!$member->isAdmin()) {
            $groups_ids = $member->getGroupsIds();
            if (count($groups_ids) == 0) return [];
            $groups_ids = implode(",", $groups_ids);
            $builder->andWhere("g.id in ({$groups_ids})");
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        $res = "openstack-" . $this->name . '-';
        $res .= $this->begin_date->format('Y') . '-summit';
        $res = strtolower(preg_replace('/[^a-zA-Z0-9\-]/', '', $res));
        return $res;
    }

    /**
     * @return int
     */
    public function getPresentationVotesCount()
    {


        try {
            $sql = <<<SQL
            SELECT COUNT(DISTINCT(Vote.ID)) AS vote_count
            FROM PresentationVote AS Vote
            INNER JOIN SummitEvent AS E ON E.ID = Vote.PresentationID
            WHERE E.SummitID = :summit_id
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : 0;
        } catch (\Exception $ex) {

        }
        return 0;
    }

    /**
     * @return int
     */
    public function getPresentationVotersCount()
    {
        try {
            $sql = <<<SQL
                SELECT COUNT(DISTINCT(Vote.MemberID)) AS voter_count
            FROM PresentationVote AS Vote
            INNER JOIN SummitEvent AS E ON E.ID = Vote.PresentationID
            WHERE E.SummitID = :summit_id
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : 0;
        } catch (\Exception $ex) {

        }
        return 0;
    }

    /**
     * @return int
     */
    public function getAttendeesCount()
    {
        return $this->attendees->count();
    }

    /**
     * @return int
     */
    public function getSpeakersCount()
    {
        return count($this->getSpeakers());
    }

    /**
     * @return int
     */
    public function getPresentationsSubmittedCount()
    {

        try {
            $sql = <<<SQL
            SELECT COUNT(DISTINCT(SummitEvent.ID))
            FROM SummitEvent
            INNER JOIN Presentation ON Presentation.ID = SummitEvent.ID
            WHERE SummitEvent.SummitID = :summit_id AND Presentation.Status = :status
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id, 'status' => Presentation::STATUS_RECEIVED]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : 0;
        } catch (\Exception $ex) {

        }
        return 0;
    }

    /**
     * @return int
     */
    public function getPublishedEventsCount()
    {
        try {
            $sql = <<<SQL
            SELECT COUNT(DISTINCT(SummitEvent.ID))
            FROM SummitEvent
            WHERE SummitEvent.SummitID = :summit_id AND SummitEvent.Published = 1
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : 0;
        } catch (\Exception $ex) {

        }
        return 0;
    }

    // speakers emails info

    /**
     * @param strign $type
     * @return int
     */
    public function getSpeakerAnnouncementEmailCount($type)
    {
        try {
            $sql = <<<SQL
            SELECT COUNT(DISTINCT(SpeakerAnnouncementSummitEmail.ID))
            FROM SpeakerAnnouncementSummitEmail
            WHERE SpeakerAnnouncementSummitEmail.SummitID = :summit_id AND SpeakerAnnouncementSummitEmail.AnnouncementEmailTypeSent = :type
SQL;
            $stmt = $this->prepareRawSQL($sql);
            $stmt->execute(['summit_id' => $this->id, 'type' => $type]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ? $res[0] : 0;
        } catch (\Exception $ex) {

        }
        return 0;
    }

    /**
     * @return int
     */
    public function getSpeakerAnnouncementEmailAcceptedCount()
    {
        return $this->getSpeakerAnnouncementEmailCount('ACCEPTED');
    }

    /**
     * @return int
     */
    public function getSpeakerAnnouncementEmailRejectedCount()
    {
        return $this->getSpeakerAnnouncementEmailCount('REJECTED');
    }

    /**
     * @return int
     */
    public function getSpeakerAnnouncementEmailAlternateCount()
    {
        return $this->getSpeakerAnnouncementEmailCount('ALTERNATE');
    }

    /**
     * @return int
     */
    public function getSpeakerAnnouncementEmailAcceptedAlternateCount()
    {
        return $this->getSpeakerAnnouncementEmailCount('ACCEPTED_ALTERNATE');
    }

    /**
     * @return int
     */
    public function getSpeakerAnnouncementEmailAcceptedRejectedCount()
    {
        return $this->getSpeakerAnnouncementEmailCount('ACCEPTED_REJECTED');
    }

    /**
     * @return int
     */
    public function getSpeakerAnnouncementEmailAlternateRejectedCount()
    {
        return $this->getSpeakerAnnouncementEmailCount('ALTERNATE_REJECTED');
    }

    /**
     * @param SummitRegistrationPromoCode $promo_code
     */
    public function addPromoCode(SummitRegistrationPromoCode $promo_code)
    {
        $this->promo_codes->add($promo_code);
        $promo_code->setSummit($this);
    }

    /**
     * @param string $code
     * @return SummitRegistrationPromoCode|null
     */
    public function getPromoCodeByCode($code)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('code', trim($code)));
        $promo_code = $this->promo_codes->matching($criteria)->first();
        return $promo_code === false ? null : $promo_code;
    }

    /**
     * @param int $promo_code_id
     * @return SummitRegistrationPromoCode|null
     */
    public function getPromoCodeById($promo_code_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', $promo_code_id));
        $promo_code = $this->promo_codes->matching($criteria)->first();
        return $promo_code === false ? null : $promo_code;
    }

    /**
     * @param SummitRegistrationPromoCode $promo_code
     * @return $this
     */
    public function removePromoCode(SummitRegistrationPromoCode $promo_code)
    {
        $this->promo_codes->removeElement($promo_code);
        $promo_code->clearSummit();
        return $this;
    }

    /**
     * @param SummitEventType $event_type
     * @return $this
     */
    public function removeEventType(SummitEventType $event_type)
    {
        $this->event_types->removeElement($event_type);
        $event_type->clearSummit();
        return $this;
    }

    /**
     * @return PresentationCategory[]
     */
    public function getExcludedCategoriesForAcceptedPresentations()
    {
        return $this->excluded_categories_for_accepted_presentations->toArray();
    }

    /**
     * @return PresentationCategory[]
     */
    public function getExcludedCategoriesForAlternatePresentations()
    {
        return $this->excluded_categories_for_alternate_presentations->toArray();
    }

    /**
     * @return PresentationCategory[]
     */
    public function getExcludedCategoriesForRejectedPresentations()
    {
        return $this->excluded_categories_for_rejected_presentations->toArray();
    }

    /**
     * @return PresentationCategory[]
     */
    public function getExcludedCategoriesForUploadSlideDecks()
    {
        return $this->excluded_categories_for_upload_slide_decks->toArray();
    }

    /**
     * @param SummitEventType $event_type
     * @return $this
     */
    public function addEventType(SummitEventType $event_type)
    {
        $this->event_types->add($event_type);
        $event_type->setSummit($this);
        return $this;
    }

    /**
     * @param SummitTicketType $ticket_type
     * @return $this
     */
    public function addTicketType(SummitTicketType $ticket_type)
    {
        $this->ticket_types->add($ticket_type);
        $ticket_type->setSummit($this);
        return $this;
    }


    /**
     * @param SummitTicketType $ticket_type
     * @return $this
     */
    public function removeTicketType(SummitTicketType $ticket_type)
    {
        $this->ticket_types->removeElement($ticket_type);
        $ticket_type->clearSummit();
        return $this;
    }

    /**
     * @param string $name
     * @return SummitTicketType|null
     */
    public function getTicketTypeByName($name){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', trim($name)));
        $res = $this->ticket_types->matching($criteria)->first();
        return $res === false ? null : $res;
    }


    /**
     * @param int $id
     * @return SummitTicketType|null
     */
    public function getTicketTypeById($id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($id)));
        $res = $this->ticket_types->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @param int $rsvp_template_id
     * @return RSVPTemplate|null
     */
    public function getRSVPTemplateById($rsvp_template_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($rsvp_template_id)));
        $rsvp_template = $this->rsvp_templates->matching($criteria)->first();
        return $rsvp_template === false ? null : $rsvp_template;
    }

    /**
     * @param string $rsvp_template_title
     * @return RSVPTemplate|null
     */
    public function getRSVPTemplateByTitle($rsvp_template_title){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('title', trim($rsvp_template_title)));
        $rsvp_template = $this->rsvp_templates->matching($criteria)->first();
        return $rsvp_template === false ? null : $rsvp_template;
    }

    /**
     * @param RSVPTemplate $template
     * @return $this
     */
    public function addRSVPTemplate(RSVPTemplate $template){
        if($this->rsvp_templates->contains($template)) return;
        $this->rsvp_templates->add($template);
        $template->setSummit($this);
        return $this;
    }

    /**
     * @param RSVPTemplate $template
     * @return $this
     */
    public function removeRSVPTemplate(RSVPTemplate $template){
        if(!$this->rsvp_templates->contains($template)) return;
        $this->rsvp_templates->removeElement($template);
        $template->clearSummit();
        return $this;
    }

    /**
     * @param SummitAbstractLocation $location
     * @param int $new_order
     * @throws ValidationException
     */
    public function recalculateLocationOrder(SummitAbstractLocation $location, $new_order){

        $former_order = $location->getOrder();
        $criteria     = Criteria::create();
        $criteria->orderBy(['order'=> 'ASC']);
        $filtered_locations = [];

        foreach($this->locations->matching($criteria)->toArray() as $l){
            if(Summit::isPrimaryLocation($l))
                $filtered_locations[] = $l;
        }

        $filtered_locations = array_slice($filtered_locations,0, count($filtered_locations), false);
        $max_order          = count($filtered_locations);

        if($new_order > $max_order)
            throw new ValidationException(sprintf("max order is %s", $max_order));

        unset($filtered_locations[$former_order - 1]);

        $filtered_locations = array_merge
        (
            array_slice($filtered_locations, 0, $new_order -1 , true) ,
            [$location] ,
            array_slice($filtered_locations, $new_order -1 , count($filtered_locations), true)
        );

        $order = 1;
        foreach($filtered_locations as $l){
            $l->setOrder($order);
            $order++;
        }
    }

    /**
     * @param SummitAbstractLocation $location
     * @return int[]
     */
    public function getScheduleEventsIdsPerLocation(SummitAbstractLocation $location){
        $query = <<<SQL
SELECT e.id  
FROM  models\summit\SummitEvent e
WHERE 
e.published = 1
AND e.summit = :summit
AND e.location = :location
SQL;

        $native_query = $this->getEM()->createQuery($query);

        $native_query->setParameter("summit", $this);
        $native_query->setParameter("location", $location);

        $res =  $native_query->getResult();

        return $res;
    }

    /**
     * @param SummitAbstractLocation $location
     * @return $this
     */
    public function removeLocation(SummitAbstractLocation $location){
        $this->locations->removeElement($location);
        $location->setSummit(null);
        return $this;
    }

    /**
     * @param string $calendar_sync_name
     */
    public function setCalendarSyncName($calendar_sync_name)
    {
        $this->calendar_sync_name = $calendar_sync_name;
    }

    /**
     * @param string $calendar_sync_desc
     */
    public function setCalendarSyncDesc($calendar_sync_desc)
    {
        $this->calendar_sync_desc = $calendar_sync_desc;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getRegistrationLink()
    {
        return $this->registration_link;
    }

    /**
     * @param string $registration_link
     */
    public function setRegistrationLink($registration_link)
    {
        $this->registration_link = $registration_link;
    }

    /**
     * @return int
     */
    public function getMaxSubmissionAllowedPerUser()
    {
        return $this->max_submission_allowed_per_user;
    }

    /**
     * @param int $max_submission_allowed_per_user
     */
    public function setMaxSubmissionAllowedPerUser($max_submission_allowed_per_user)
    {
        $this->max_submission_allowed_per_user = $max_submission_allowed_per_user;
    }

    /**
     * @param SummitPushNotification $notification
     * @return $this
     */
    public function addNotification(SummitPushNotification $notification){
        $this->notifications->add($notification);
        $notification->setSummit($this);
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeZoneId()
    {
        return $this->time_zone_id;
    }

    /**
     * @param string $time_zone_id
     */
    public function setTimeZoneId($time_zone_id)
    {
        $this->time_zone_id = $time_zone_id;
    }

    /**
     * @return string
     */
    public function getSecondaryRegistrationLink()
    {
        return $this->secondary_registration_link;
    }

    /**
     * @param string $secondary_registration_link
     */
    public function setSecondaryRegistrationLink($secondary_registration_link)
    {
        $this->secondary_registration_link = $secondary_registration_link;
    }

    /**
     * @return string
     */
    public function getSecondaryRegistrationLabel()
    {
        return $this->secondary_registration_label;
    }

    /**
     * @param string $secondary_registration_label
     */
    public function setSecondaryRegistrationLabel($secondary_registration_label)
    {
        $this->secondary_registration_label = $secondary_registration_label;
    }

    /**
     * @param int $notification_id
     * @return SummitPushNotification|null
     */
    public function getNotificationById($notification_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($notification_id)));
        $notification = $this->notifications->matching($criteria)->first();
        return $notification === false ? null : $notification;
    }

    /**
     * @param SummitPushNotification $notification
     * @return $this
     */
    public function removeNotification(SummitPushNotification $notification){
        $this->notifications->removeElement($notification);
        $notification->clearSummit();
        return $this;
    }

    /**
     * @return string
     */
    public function getCalendarSyncName()
    {
        return $this->calendar_sync_name;
    }

    /**
     * @return string
     */
    public function getCalendarSyncDesc()
    {
        return $this->calendar_sync_desc;
    }

    /**
     * @return DateTime
     */
    public function getRegistrationBeginDate()
    {
        return $this->registration_begin_date;
    }

    /**
     * @param DateTime $registration_begin_date
     */
    public function setRegistrationBeginDate(DateTime $registration_begin_date){
        $this->registration_begin_date = $this->convertDateFromTimeZone2UTC($registration_begin_date);
    }

    /**
     * @return $this
     */
    public function clearRegistrationDates(){
        $this->registration_begin_date = $this->registration_end_date = null;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRegistrationEndDate()
    {
        return $this->registration_end_date;
    }

    /**
     * @param DateTime $registration_end_date
     */
    public function setRegistrationEndDate(DateTime $registration_end_date){
        $this->registration_end_date = $this->convertDateFromTimeZone2UTC($registration_end_date);
    }

    /**
     * @return SelectionPlan[]
     */
    public function getSelectionPlans()
    {
        return $this->selection_plans;
    }

    /**
     * @param SelectionPlan $selection_plan
     * @throws ValidationException
     * @return bool
     */
    public function checkSelectionPlanConflicts(SelectionPlan $selection_plan){
        foreach ($this->selection_plans as $sp){

            if($sp->getId() == $selection_plan->getId()) continue;

            $start1 = $selection_plan->getSelectionBeginDate();
            $end1   = $selection_plan->getSelectionEndDate();
            $start2 = $sp->getSelectionBeginDate();
            $end2   = $sp->getSelectionEndDate();

            if(!is_null($start1) && !is_null($end1) &&
               !is_null($start2) && !is_null($end2)
                && DateUtils::checkTimeFramesOverlap
                (
                    $start1,
                    $end1,
                    $start2,
                    $end2
                )
            )
                throw new ValidationException(trans(
                    'validation_errors.Summit.checkSelectionPlanConflicts.conflictOnSelectionWorkflow',
                    [
                        'selection_plan_id' => $sp->getId(),
                        'summit_id' => $this->getId()
                    ]
                ));

            $start1 = $selection_plan->getSubmissionBeginDate();
            $end1   = $selection_plan->getSubmissionEndDate();
            $start2 = $sp->getSubmissionBeginDate();
            $end2   = $sp->getSubmissionEndDate();

            if(!is_null($start1) && !is_null($end1) &&
                !is_null($start2) && !is_null($end2) &&
                DateUtils::checkTimeFramesOverlap
                (
                    $start1,
                    $end1,
                    $start2,
                    $end2

                )
            )
                throw new ValidationException(trans(
                    'validation_errors.Summit.checkSelectionPlanConflicts.conflictOnSubmissionWorkflow',
                    [
                        'selection_plan_id' => $sp->getId(),
                        'summit_id' => $this->getId()
                    ]
                ));

            $start1 = $selection_plan->getVotingBeginDate();
            $end1   = $selection_plan->getVotingEndDate();
            $start2 = $sp->getVotingBeginDate();
            $end2   = $sp->getVotingEndDate();

            if(!is_null($start1) && !is_null($end1) &&
                !is_null($start2) && !is_null($end2) &&
                DateUtils::checkTimeFramesOverlap
                (
                    $start1,
                    $end1,
                    $start2,
                    $end2
                )
            )
                throw new ValidationException(trans(
                    'validation_errors.Summit.checkSelectionPlanConflicts.conflictOnVotingWorkflow',
                    [
                        'selection_plan_id' => $sp->getId(),
                        'summit_id' => $this->getId()
                    ]
                ));
        }

        return true;
    }

    /**
     * @param string $name
     * @return null|SelectionPlan
     */
    public function getSelectionPlanByName($name){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', trim($name)));
        $selection_plan = $this->selection_plans->matching($criteria)->first();
        return $selection_plan === false ? null : $selection_plan;
    }

    /**
     * @param string $status
     * @return null|SelectionPlan
     */
    public function getCurrentSelectionPlanByStatus($status){
        $now_utc = new \DateTime('now', new \DateTimeZone('UTC'));
        $criteria = Criteria::create();
        switch (strtoupper($status)){
            case SelectionPlan::STATUS_SUBMISSION:{
                $criteria->where(Criteria::expr()->lte('submission_begin_date', $now_utc))->andWhere(Criteria::expr()->gte('submission_end_date', $now_utc));
            }
            break;
            case SelectionPlan::STATUS_VOTING:{
                $criteria->where(Criteria::expr()->lte('voting_begin_date', $now_utc))->andWhere(Criteria::expr()->gte('voting_end_date', $now_utc));
            }
            break;
            case SelectionPlan::STATUS_SELECTION:{
                $criteria->where(Criteria::expr()->lte('selection_begin_date', $now_utc))->andWhere(Criteria::expr()->gte('selection_end_date', $now_utc));
            }
            break;
        }
        $selection_plan = $this->selection_plans->matching($criteria)->first();
        return $selection_plan === false ? null : $selection_plan;
    }

    /**
     * @param int $id
     * @return null|SelectionPlan
     */
    public function getSelectionPlanById($id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($id)));
        $selection_plan = $this->selection_plans->matching($criteria)->first();
        return $selection_plan === false ? null : $selection_plan;
    }

    /**
     * @param SelectionPlan $selection_plan
     * @return $this
     */
    public function addSelectionPlan(SelectionPlan $selection_plan){
        $this->selection_plans->add($selection_plan);
        $selection_plan->setSummit($this);
        return $this;
    }

    /**
     * @param SelectionPlan $selection_plan
     * @return $this
     */
    public function removeSelectionSelectionPlan(SelectionPlan $selection_plan){
        $this->selection_plans->removeElement($selection_plan);
        $selection_plan->clearSummit();
        return $this;
    }

    /**
     * @return SelectionPlan[]
     */
    public function getActiveSelectionPlans() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('is_enabled', 1));
        return $this->selection_plans->matching($criteria)->toArray();
    }

    /**
     * @return bool
     */
    public function isSubmissionOpen()
    {
        foreach ($this->getActiveSelectionPlans() as $plan) {
            if ($plan->isSubmissionOpen())
                return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isPresentationEditionAllowed()
    {
        return $this->isSubmissionOpen() || $this->isVotingOpen();
    }

    /**
     * @return bool
     */
    public function isVotingOpen()
    {
        foreach ($this->getActiveSelectionPlans() as $plan) {
            if ($plan->isVotingOpen()) {
                return true;
            }
        }
        return false;
    }

    const STAGE_UNSTARTED = -1;
    const STAGE_OPEN = 0;
    const STAGE_FINISHED = 1;

}
