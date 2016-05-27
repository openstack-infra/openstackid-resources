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
use libs\utils\DateTimeUtils;
use models\exceptions\ValidationException;
use models\main\Tag;
use models\utils\SilverstripeBaseModel;
use utils\Filter;
use utils\Order;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;
use utils\PagingResponse;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEvent")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitEvent" = "SummitEvent", "Presentation" = "Presentation"})
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineSummitEventRepository")
 * Class SummitEvent
 * @package models\summit
 */
class SummitEvent extends SilverstripeBaseModel
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
        $this->tags         = new ArrayCollection();
        $this->summit_types = new ArrayCollection();
        $this->feedback     = new ArrayCollection();
        $this->attendees    = new ArrayCollection();
        $this->sponsors     = new ArrayCollection();
    }

   /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    protected $title;

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
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(name="ShortDescription", type="string")
     * @var string
     */
    protected $short_description;

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @ORM\Column(name="StartDate", type="datetime")
     * @var \DateTime
     */
    protected $start_date;

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * @param string $short_description
     */
    public function setShortDescription($short_description)
    {
        $this->short_description = $short_description;
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
        return $this->avg_feedback;
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
    public function getRsvpLink()
    {
        return $this->rsvp_link;
    }

    /**
     * @param string $rsvp_link
     */
    public function setRsvpLink($rsvp_link)
    {
        $this->rsvp_link = $rsvp_link;
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
     * @ORM\Column(name="RSVPLink", type="string")
     * @var string
     */
    protected $rsvp_link;


    /**
     * @ORM\Column(name="HeadCount", type="integer")
     * @var int
     */
    protected $head_count;


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
            return $this->location->getId();
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
        $value = $this->start_date;
        if(!empty($value)) {
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
        $value = $this->end_date;
        if(!empty($value)) {
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
            return $this->type->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @ORM\ManyToOne(targetEntity="SummitEventType")
     * @ORM\JoinColumn(name="TypeID", referencedColumnName="ID")
     */
    private $type;

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
     * @ORM\ManyToOne(targetEntity="SummitAbstractLocation")
     * @ORM\JoinColumn(name="LocationID", referencedColumnName="ID")
     */
    private $location;

    /**
     * @param SummitAbstractLocation $location
     * @return $this
     */
    public function setLocation(SummitAbstractLocation $location)
    {
        $this->location = $location;
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
    public function getSummitTypesIds()
    {
        return $this->summit_types->map(function($entity)  {
            return $entity->getId();
        })->toArray();
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
     * @ORM\ManyToMany(targetEntity="models\summit\SummitType")
     * @ORM\JoinTable(name="SummitEvent_AllowedSummitTypes",
     *      joinColumns={@ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="SummitTypeID", referencedColumnName="ID")}
     *      )
     */
    protected $summit_types;

    /**
     * @return ArrayCollection
     */
    public function getSummitTypes(){
        return $this->summit_types;
    }

    /**
     * @param SummitType $summit_type
     */
    public function addSummitType(SummitType $summit_type)
    {
        $this->summit_types->add($summit_type);
    }

    public function clearSummitTypes()
    {
        $this->summit_types->clear();
    }

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Company", inversedBy="sponsorships")
     * @ORM\JoinTable(name="SummitEvent_Sponsors",
     *      joinColumns={@ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="CompanyID", referencedColumnName="ID")}
     *      )
     */
    protected $sponsors;

    /**
     * @return Company[]
     */
    public function getSponsors()
    {
        return $this->sponsors;
    }

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\SummitAttendee")
     * @ORM\JoinTable(name="SummitAttendee_Schedule",
     *      joinColumns={@ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="SummitAttendeeID", referencedColumnName="ID")}
     *      )
     */
    protected $attendees;

    /**
     * @return SummitAttendee[]
     */
    public function getAttendees()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('IsCheckedIn', 1));
        return $this->attendees->matching($criteria);
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventFeedback", mappedBy="event", cascade={"persist"})
     * @var SummitEventFeedback[]
     */
    protected $feedback;

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
     * @ORM\ManyToMany(targetEntity="models\main\Tag")
     * @ORM\JoinTable(name="SummitEvent_Tags",
     *      joinColumns={@ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="TagID", referencedColumnName="ID")}
     *      )
     */
    protected $tags;

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
        $this->tags->add($tag);
    }

    public function clearTags()
    {
        $this->tags->clear();
    }

    /**
     * @throws EntityValidationException
     * @throws ValidationException
     * @return void
     */
    public function publish()
    {
        if($this->published)
            throw new ValidationException('Already published Summit Event');

        if(count($this->summit_types()) === 0)
            throw new EntityValidationException('To publish this event you must associate a valid summit type!');

        $start_date = $this->start_date;
        $end_date   = $this->end_date;

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
                    $summit->getLocalBeginDate(),
                    $summit->getLocalEndDate()
                )
            );

        $this->published     = true;
        $this->published_date = DateTimeUtils::nowRfc2822();
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

}