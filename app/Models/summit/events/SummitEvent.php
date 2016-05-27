<?php
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

namespace models\summit;

use Doctrine\Common\Collections\Criteria;
use libs\utils\DateTimeUtils;
use models\exceptions\ValidationException;
use models\main\Tag;
use models\utils\SilverstripeBaseModel;
use utils\Filter;
use utils\Order;
use libs\utils\JsonUtils;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEvent")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitEvent" = "SummitEvent", "Presentation" = "Presentation"})
 * Class SummitEvent
 * @package models\summit
 */
class SummitEvent extends SilverstripeBaseModel
{

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

    protected $from_attendee;

    protected static $array_mappings = array
    (
        'ID'              => 'id:json_int',
        'Title'           => 'title:json_string',
        'Description'     => 'description:json_string',
        'StartDate'       => 'start_date:datetime_epoch',
        'EndDate'         => 'end_date:datetime_epoch',
        'LocationID'      => 'location_id:json_int',
        'SummitID'        => 'summit_id:json_int',
        'TypeID'          => 'type_id:json_int',
        'ClassName'       => 'class_name',
        'AllowFeedBack'   => 'allow_feedback:json_boolean',
        'AvgFeedbackRate' => 'avg_feedback_rate:json_float',
        'Published'       => 'is_published:json_boolean',
        'HeadCount'       => 'head_count:json_int',
        'RSVPLink'        => 'rsvp_link:json_string',
    );

    public static $allowed_fields = array
    (
        'id',
        'title',
        'description',
        'start_date',
        'end_date',
        'location_id',
        'summit_id',
        'type_id',
        'class_name',
        'allow_feedback',
        'avg_feedback_rate',
        'is_published',
        'head_count',
        'rsvp_link',
    );

    public static $allowed_relations = array
    (
        'summit_types',
        'sponsors',
        'tags',
    );

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ID", type="integer", unique=true, nullable=false)
     */
    protected $id;

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
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    protected $description;

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
        $this->summit_types->getKeys();
    }

    /**
     * @return array
     */
    public function getSponsorsIds()
    {
        return $this->sponsors->getKeys();
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
     * @ORM\ManyToMany(targetEntity="models\main\Company")
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

    public function setFromAttendee()
    {
        $this->from_attendee = true;
        $this->array_mappings['IsCheckedIn'] = 'is_checked_in:json_boolean';
    }

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventFeedback", mappedBy="event", cascade={"persist"})
     */
    protected $feedback;

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @param Order|null $order
     * @return array
     */
    public function getFeedback($page = 1, $per_page = 100, Filter $filter = null, Order $order = null)
    {
        $rel = $this->hasMany('models\summit\SummitEventFeedback', 'EventID', 'ID')->where('ClassName','=','SummitEventFeedback');

        if(!is_null($filter))
        {
            $filter->apply2Relation($rel, array
            (
                'owner_id'      => 'SummitEventFeedback.OwnerID',
            ));
        }

        if(!is_null($order))
        {
            $order->apply2Relation($rel, array
            (
                'created_date' => 'SummitEventFeedback.Created',
                'owner_id'     => 'SummitEventFeedback.OwnerID',
                'rate'         => 'SummitEventFeedback.Rate',
                'id'           => 'SummitEventFeedback.ID',
            ));
        }
        else
        {
            //default order
            $rel = $rel->orderBy('SummitEventFeedback.Created', 'DESC');
        }

        $pagination_result = $rel->paginate($per_page);
        $total             = $pagination_result->total();
        $items             = $pagination_result->items();
        $per_page          = $pagination_result->perPage();
        $current_page      = $pagination_result->currentPage();
        $last_page         = $pagination_result->lastPage();

        $feedback = array();
        foreach($items as $e)
        {
            array_push($feedback, $e);
        }
        return array($total,$per_page, $current_page, $last_page, $feedback);
    }

    public function addFeedBack(SummitEventFeedback $feedback)
    {
       $this->feedback->add($feedback);
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

        $timezone = $summit->TimeZone;
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

    /**
     * @param array $fields
     * @param array $relations
     * @return array
     */
    public function toArray(array $fields = array(), array $relations = array())
    {
        if(!count($fields)) $fields       = self::$allowed_fields;
        if(!count($relations)) $relations = self::$allowed_relations;

        $values = parent::toArray();
        //check if description is empty, if so, set short description
        $description = $values['description'];
        if(empty($description))
        {
            $values['description'] = JsonUtils::toJsonString($this->ShortDescription);
        }

        //check requested fields

        foreach($values as $field => $value){
            if(in_array($field, $fields)) continue;
            unset($values[$field]);
        }

        if(in_array('summit_types', $relations))
            $values['summit_types'] = $this->getSummitTypesIds();

        if(in_array('sponsors', $relations))
            $values['sponsors']     = $this->getSponsorsIds();

        if(in_array('tags', $relations))
        {
            $tags = array();
            foreach ($this->tags() as $t) {
                array_push($tags, $t->toArray());
            }
            $values['tags'] = $tags;
        }

        return $values;
    }
}