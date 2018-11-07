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
use App\Models\Foundation\Summit\Events\RSVP\RSVPTemplate;
use App\Events\SummitEventCreated;
use App\Events\SummitEventDeleted;
use App\Events\SummitEventUpdated;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use models\exceptions\ValidationException;
use models\main\Company;
use models\main\Tag;
use models\utils\PreRemoveEventArgs;
use models\utils\SilverstripeBaseModel;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitEventRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(
 *          name="summit",
 *          inversedBy="events"
 *     )
 * })
 * @ORM\Table(name="SummitEvent")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitEvent" = "SummitEvent", "Presentation" = "Presentation", "SummitGroupEvent" = "SummitGroupEvent", "SummitEventWithFile" = "SummitEventWithFile"})
 * @ORM\HasLifecycleCallbacks
 * Class SummitEvent
 * @package models\summit
 */
class SummitEvent extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="Abstract", type="string")
     * @var string
     */
    protected $abstract;

    /**
     * @ORM\Column(name="SocialSummary", type="string")
     * @var string
     */
    protected $social_summary;

    /**
     * @ORM\Column(name="Occupancy", type="string")
     * @var string
     */
    protected $occupancy;

    /**
     * @ORM\Column(name="StartDate", type="datetime")
     * @var \DateTime
     */
    protected $start_date;

    /**
     * @ORM\Column(name="EndDate", type="datetime")
     * @var \DateTime
     */
    protected $end_date;

    /**
     * @ORM\Column(name="Published", type="boolean")
     * @var bool
     */
    protected $published;

    /**
     * @ORM\Column(name="PublishedDate", type="datetime")
     * @var \DateTime
     */
    protected $published_date;

    /**
     * @ORM\Column(name="AllowFeedBack", type="boolean")
     * @var bool
     */
    protected $allow_feedback;

    /**
     * @ORM\Column(name="AvgFeedbackRate", type="float")
     * @var float
     */
    protected $avg_feedback;

    /**
     * @ORM\Column(name="HeadCount", type="integer")
     * @var int
     */
    protected $head_count;

    /**
     * @ORM\Column(name="RSVPMaxUserNumber", type="integer")
     * @var int
     */
    protected $rsvp_max_user_number;

    /**
     * @ORM\Column(name="RSVPMaxUserWaitListNumber", type="integer")
     * @var int
     */
    protected $rsvp_max_user_wait_list_number;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\Foundation\Summit\Events\RSVP\RSVPTemplate", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="RSVPTemplateID", referencedColumnName="ID", onDelete="SET NULL")
     * @var RSVPTemplate
     */
    protected $rsvp_template;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\RSVP", mappedBy="event", cascade={"persist"})
     * @var RSVP[]
     */
    protected $rsvp;

    /**
     * @ORM\Column(name="RSVPLink", type="string")
     * @var string
     */
    protected $rsvp_link;

    /**
     * @ORM\ManyToOne(targetEntity="PresentationCategory", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="CategoryID", referencedColumnName="ID", onDelete="SET NULL")
     * @var PresentationCategory
     */
    protected $category = null;

    /**
     * @ORM\ManyToOne(targetEntity="SummitEventType", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="TypeID", referencedColumnName="ID", onDelete="SET NULL")
     * @var SummitEventType
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="SummitAbstractLocation", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="LocationID", referencedColumnName="ID", onDelete="SET NULL")
     * @var SummitAbstractLocation
     */
    protected $location = null;

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Company", inversedBy="sponsorships", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="SummitEvent_Sponsors",
     *      joinColumns={@ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="CompanyID", referencedColumnName="ID", onDelete="CASCADE")}
     *      )
     */
    protected $sponsors;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventFeedback", mappedBy="event", cascade={"persist"})
     * @ORM\Cache("NONSTRICT_READ_WRITE")
     * @var SummitEventFeedback[]
     */
    protected $feedback;

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="SummitEvent_Tags",
     *      joinColumns={@ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="TagID", referencedColumnName="ID")}
     *      )
     */
    protected $tags;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return boolean
     */
    public function isAllowFeedback()
    {
        return $this->getAllowFeedback();
    }

    /**
     * @return boolean
     */
    public function getAllowFeedback()
    {
        return $this->allow_feedback;
    }

    use SummitOwned;

    /**
     * SummitEvent constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->allow_feedback = false;
        $this->published      = false;
        $this->avg_feedback   = 0;
        $this->head_count     = 0;
        $this->rsvp_max_user_number = 0;
        $this->rsvp_max_user_wait_list_number = 0;

        $this->tags           = new ArrayCollection();
        $this->feedback       = new ArrayCollection();
        $this->sponsors       = new ArrayCollection();
        $this->rsvp           = new ArrayCollection();
    }

    /**
     * @param PresentationCategory $category
     * @return $this
     */
    public function setCategory(PresentationCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return PresentationCategory
     */
    public function getCategory(){
        return $this->category;
    }

    /**
     * @return int
     */
    public function getCategoryId(){
        try {
            return !is_null($this->category)? $this->category->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return "SummitEvent";
    }

    /**
     * @return string
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param string $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * @return string
     */
    public function getSocialSummary()
    {
        return $this->social_summary;
    }

    /**
     * @param string $social_summary
     */
    public function setSocialSummary($social_summary)
    {
        $this->social_summary = $social_summary;
    }

    /**
     * @return DateTime
     */
    public function getPublishedDate()
    {
        return $this->published_date;
    }

    /**
     * @param DateTime $published_date
     */
    public function setPublishedDate($published_date)
    {
        $this->published_date = $published_date;
    }

    /**
     * @return float
     */
    public function getAvgFeedbackRate()
    {
        return !is_null($this->avg_feedback) ? $this->avg_feedback : 0.0;
    }

    /**
     * @param float $avg_feedback
     */
    public function setAvgFeedbackRate($avg_feedback)
    {
        $this->avg_feedback = $avg_feedback;
    }

    /**
     * @return string
     */
    public function getRSVPLink()
    {
        if($this->hasRSVPTemplate()){

            $summit         = $this->getSummit();
            $schedule_page  = $summit->getSchedulePage();
            if(empty($schedule_page)) return '';
            $url = sprintf("%s%s/events/%s/%s/rsvp",
                Config::get("server.assets_base_url", 'https://www.openstack.org/'),
                $schedule_page,
                $this->getId(),
                $this->getSlug()
            );
            return $url;
        }
        return $this->rsvp_link;
    }

    /**
     * @return bool
     */
    public function hasRSVPTemplate(){
        return $this->getRSVPTemplateId() > 0;
    }

    /**
     * @return int
     */
    public function getRSVPTemplateId(){
        try{
            return !is_null($this->rsvp_template) ? $this->rsvp_template->getId() : 0;
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @return RSVPTemplate
     */
    public function getRSVPTemplate()
    {
        return $this->rsvp_template;
    }

    /**
     * @param RSVPTemplate $rsvp_template
     */
    public function setRSVPTemplate(RSVPTemplate $rsvp_template)
    {
        $this->rsvp_template = $rsvp_template;
        $this->rsvp_link     = '';
    }

    /**
     * @return bool
     */
    public function hasRSVP(){
        return !empty($this->rsvp_link) || $this->hasRSVPTemplate();
    }

    /**
     * @return bool
     */
    public function isExternalRSVP(){
        return !empty($this->rsvp_link) && !$this->hasRSVPTemplate();
    }

    public function getSlug(){
        $slugify = new Slugify();
        return $slugify->slugify($this->title);
    }

    /**
     * @param string $rsvp_link
     */
    public function setRSVPLink($rsvp_link)
    {
        $this->rsvp_link     = $rsvp_link;
        $this->rsvp_template = null;
        $this->rsvp_max_user_wait_list_number = 0;
        $this->rsvp_max_user_number = 0;
    }

    /**
     * @return int
     */
    public function getHeadCount()
    {
        return $this->head_count;
    }

    /**
     * @param int $head_count
     */
    public function setHeadCount($head_count)
    {
        $this->head_count = $head_count;
    }

    /**
     * @return bool
     */
    public function hasLocation(){
        return $this->getLocationId() > 0;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        try {
            return !is_null($this->location)? $this->location->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param DateTime $value
     * @return $this
     */
    public function setStartDate(DateTime $value)
    {
        $summit = $this->getSummit();
        if(!is_null($summit))
        {
            $value = $summit->convertDateFromTimeZone2UTC($value);
        }
        $this->start_date = $value;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLocalStartDate()
    {
        if(!empty($this->start_date)) {
            $value  = clone $this->start_date;
            $summit = $this->getSummit();
            if(!is_null($summit))
            {
                $res = $summit->convertDateFromUTC2TimeZone($value);
            }
            return $res;
        }
        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param DateTime $value
     * @return $this
     */
    public function setEndDate(DateTime $value)
    {
        $summit = $this->getSummit();
        if(!is_null($summit))
        {
            $value = $summit->convertDateFromTimeZone2UTC($value);
        }
        $this->end_date = $value;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLocalEndDate()
    {
        if(!empty($this->end_date)) {
            $value  = clone $this->end_date;
            $summit = $this->getSummit();
            if(!is_null($summit))
            {
                $res = $summit->convertDateFromUTC2TimeZone($value);
            }
            return $res;
        }
        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate()
    {
       return $this->end_date;
    }

    /**
     * @param bool $allow_feeback
     * @return $this
     */
    public function setAllowFeedBack($allow_feeback)
    {
        $this->allow_feedback = $allow_feeback;
        return $this;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        try {
            return !is_null($this->type) ? $this->type->getId() : 0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param SummitEventType $type
     * @return $this
     */
    public function setType(SummitEventType $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return SummitEventType
     */
    public function getType(){
        return $this->type;
    }

    /**
     * @param SummitAbstractLocation $location
     * @return $this
     */
    public function setLocation(SummitAbstractLocation $location)
    {
        $this->location = $location;
        return $this;
    }

    public function clearLocation(){
        $this->location = null;
        return $this;
    }

    /**
     * @return SummitAbstractLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return array
     */
    public function getSponsorsIds()
    {
        return $this->sponsors->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @return Company[]
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }

    /**
     * @param Company $sponsor
     */
    public function addSponsor(Company $sponsor){
        $this->sponsors->add($sponsor);
    }


    public function clearSponsors(){
        $this->sponsors->clear();
    }


    public function addFeedBack(SummitEventFeedback $feedback)
    {
       $this->feedback->add($feedback);
       $feedback->setEvent($this);
    }

    /**
     * @return SummitEventFeedback[]
     */
    public function getFeedback(){
        $criteria = Criteria::create();
        $criteria = $criteria->orderBy(['created' => Criteria::DESC]);
        return $this->feedback->matching($criteria);
    }

    /**
     * @return ArrayCollection
     */
    public function getTags(){
        return $this->tags;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        if($this->tags->contains($tag)) return;
        $this->tags->add($tag);
    }

    public function clearTags()
    {
        $this->tags->clear();
    }

    /**
     * @throws ValidationException
     * @return void
     */
    public function publish()
    {
        if($this->isPublished())
            throw new ValidationException('Already published Summit Event');

        $start_date = $this->getStartDate();
        $end_date   = $this->getEndDate();

        if((is_null($start_date) || is_null($end_date)))
            throw new ValidationException('To publish this event you must define a start/end datetime!');

        $summit = $this->getSummit();

        if(is_null($summit))
            throw new ValidationException('To publish you must assign a summit');

        $timezone = $summit->getTimeZoneId();

        if(empty($timezone)){
            throw new ValidationException('Invalid Summit TimeZone!');
        }

        if($end_date < $start_date)
            throw new ValidationException('start datetime must be greather or equal than end datetime!');

        if(!$summit->isEventInsideSummitDuration($this))
            throw new ValidationException
            (
                sprintf
                (
                    'start/end datetime must be between summit start/end datetime! (%s - %s)',
                    $summit->getLocalBeginDate()->format('Y-m-d H:i:s'),
                    $summit->getLocalEndDate()->format('Y-m-d H:i:s')
                )
            );

        $this->published      = true;
        $this->published_date = new DateTime();
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->getPublished();
    }

    /**
     * @return bool
     */
    public function getPublished()
    {
        return  (bool)$this->published;
    }

    /**
     * @return void
     */
    public function unPublish()
    {
        $this->published     = false;
        $this->published_date = null;
    }

    // events

    /**
     * @var PreRemoveEventArgs
     */
    private $pre_remove_events;
    /**
     * @ORM\PreRemove:
     */
    public function deleting($args){
        $this->pre_remove_events = new PreRemoveEventArgs
        (
            [
                'id'         => $this->id,
                'class_name' => $this->getClassName(),
                'summit'     => $this->summit,
                'published'  => $this->isPublished(),
            ]
        );
    }

    /**
     * @ORM\PostRemove:
     */
    public function deleted($args){

        Event::fire(new SummitEventDeleted($this,  $this->pre_remove_events ));
        $this->pre_remove_events = null;
    }

    /**
     * @var PreUpdateEventArgs
     */
    private $pre_update_args;

    /**
     * @ORM\PreUpdate:
     */
    public function updating(PreUpdateEventArgs $args){
        $this->pre_update_args = $args;
    }

    /**
     * @ORM\PostUpdate:
     */
    public function updated($args)
    {
        Event::fire(new SummitEventUpdated($this, $this->pre_update_args));
        $this->pre_update_args = null;
    }

    /**
     * @ORM\PostPersist
     */
    public function inserted($args){
        Event::fire(new SummitEventCreated($this, $args));
    }

    public function hasMetricsAvailable(){
        if(is_null($this->location)) return false;
        if(!$this->location instanceof SummitVenueRoom) return false;
        return $this->location->getMetrics()->count() > 0;
    }

    /**
     * @return SummitEventMetricsSnapshot[]
     */
    public function getMetricsSnapShots(){
        $snapshots = [];
        if(is_null($this->location)) return $snapshots;
        if(!$this->location instanceof SummitVenueRoom) return $snapshots;
        foreach($this->location->getMetrics() as $metric){
            $snapshot =  $this->getMetricsValuesByType($metric);
            if(is_null($snapshot)) continue;
            $snapshots[] = $snapshot;
        }
        return $snapshots;
    }

    /**
     * @param int $type_id
     * @return SummitEventMetricsSnapshot
     */
    public function getMetricValuesByTypeId($type_id){

        $metrics = [];
        if(is_null($this->location)) return $metrics;
        if(!$this->location instanceof SummitVenueRoom) return $metrics;

        $metric_type = $this->location->getMetricsByType($type_id);
        if(is_null($metric_type)) return $metrics;
        if(!$metric_type instanceof RoomMetricType) return $metrics;

        return $this->getMetricsValuesByType($metric_type);
    }

    /**
     * @param RoomMetricType $type
     * @return SummitEventMetricsSnapshot
     */
    private function getMetricsValuesByType(RoomMetricType $type){

        $epoch_start_date = $this->getStartDate()->getTimestamp();
        $epoch_end_date   = $this->getEndDate()->getTimestamp();

        return new SummitEventMetricsSnapshot
        (
            $this,
            $type,
            $type->getMeanValueByTimeWindow($epoch_start_date, $epoch_end_date),
            $type->getMaxValueByTimeWindow($epoch_start_date, $epoch_end_date),
            $type->getMinValueByTimeWindow($epoch_start_date, $epoch_end_date),
            $type->getCurrentValueByTimeWindow($epoch_start_date, $epoch_end_date)
        );
    }

    /**
     * @return ArrayCollection
     */
    public function getRsvp()
    {
        return $this->rsvp;
    }

    /**
     * @param ArrayCollection $rsvp
     */
    public function setRsvp($rsvp)
    {
        $this->rsvp = $rsvp;
    }

    /**
     * @return string
     */
    public function getLocationName(){
        return $this->hasLocation() ? $this->location->getName() : 'TBD';
    }

    /**
     * @return int
     */
    public function getRSVPMaxUserNumber()
    {
        return $this->rsvp_max_user_number;
    }

    /**
     * @param int $rsvp_max_user_number
     */
    public function setRSVPMaxUserNumber($rsvp_max_user_number)
    {
        $this->rsvp_max_user_number = $rsvp_max_user_number;
    }

    /**
     * @return mixed
     */
    public function getRSVPMaxUserWaitListNumber()
    {
        return $this->rsvp_max_user_wait_list_number;
    }

    /**
     * @param mixed $rsvp_max_user_wait_list_number
     */
    public function setRSVPMaxUserWaitListNumber($rsvp_max_user_wait_list_number)
    {
        $this->rsvp_max_user_wait_list_number = $rsvp_max_user_wait_list_number;
    }

    /**
     * @return string
     */
    public function getOccupancy()
    {
        return $this->occupancy;
    }

    /**
     * @param string $occupancy
     */
    public function setOccupancy($occupancy)
    {
        $this->occupancy = $occupancy;
    }


}