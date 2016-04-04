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

use libs\utils\DateTimeUtils;
use models\exceptions\ValidationException;
use models\main\Tag;
use models\utils\SilverstripeBaseModel;
use utils\Filter;
use utils\Order;
use libs\utils\JsonUtils;

/**
 * Class SummitEvent
 * @package models\summit
 */
class SummitEvent extends SilverstripeBaseModel
{

    protected $table = 'SummitEvent';

    protected $stiBaseClass = 'models\summit\SummitEvent';

    protected $mtiClassType = 'concrete';

    protected $from_attendee;

    protected $array_mappings = array
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

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->Title = $title;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->Description = $description;
        return $this;
    }

    /**
     * @param \DateTime $value
     * @return $this
     */
    public function setStartDateAttribute(\DateTime $value)
    {
        $summit = $this->getSummit();
        if(!is_null($summit))
        {
            $value = new \DateTime($summit->convertDateFromTimeZone2UTC($value->format('Y-m-d H:i:s')));
        }
        $this->attributes['StartDate'] = $value->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDateAttribute($value)
    {
        if(!empty($value)) {
            $res = new \DateTime($value);
            $summit = $this->getSummit();
            if(!is_null($summit))
            {
                $res = new \DateTime($summit->convertDateFromUTC2TimeZone($value));
            }
            return $res;
        }
        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDateUTCAttribute($value)
    {
        $value =  $this->attributes['StartDate'];
        if(!empty($value)) {
            return new \DateTime($value);
        }
        return null;
    }

    /**
     * @param \DateTime $value
     * @return $this
     */
    public function setEndDateAttribute(\DateTime $value)
    {
        $summit = $this->getSummit();
        if(!is_null($summit))
        {
            $value = new \DateTime($summit->convertDateFromTimeZone2UTC($value->format('Y-m-d H:i:s')));
        }
        $this->attributes['EndDate'] = $value->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDateAttribute($value)
    {
        if(!empty($value)) {
            $res = new \DateTime($value);
            $summit = $this->getSummit();
            if(!is_null($summit))
            {
                $res = new \DateTime($summit->convertDateFromUTC2TimeZone($value));
            }
            return $res;
        }
        return null;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDateUTCAttribute($value)
    {
        $value =  $this->attributes['EndDate'];
        if(!empty($value)) {
            return new \DateTime($value);
        }
        return null;
    }

    /**
     * @param bool $allow_feeback
     * @return $this
     */
    public function setAllowFeedBack($allow_feeback)
    {
        $this->AllowFeedBack = $allow_feeback;
        return $this;
    }

    /**
     * @param SummitEventType $type
     * @return $this
     */
    public function setType(SummitEventType $type)
    {
        $this->TypeID = $type->ID;
        return $this;
    }

    /**
     * @param SummitAbstractLocation $location
     * @return $this
     */
    public function setLocation(SummitAbstractLocation $location)
    {
        $this->LocationID = $location->ID;
        return $this;
    }

    /**
     * @return SummitAbstractLocation
     */
    public function getLocation()
    {
        $location =  $this->hasOne('models\summit\SummitAbstractLocation', 'ID', 'LocationID')->first();
        if(is_null($location)) return null;
        $class = 'models\\summit\\'.$location->ClassName;
        return $class::find($location->ID);
    }

    /**
     * @param Summit $summit
     * @return $this
     */
    public function setSummit(Summit $summit)
    {
        $this->SummitID = $summit->ID;
        return $this;
    }

    /**
     * @return array
     */
    public function getSummitTypesIds()
    {
        $ids = array();
        foreach($this->summit_types() as $type)
        {
            array_push($ids, intval($type->ID));
        }
        return $ids;
    }

    /**
     * @return array
     */
    public function getSponsorsIds()
    {
        $ids = array();
        foreach($this->sponsors() as $company)
        {
            array_push($ids, intval($company->ID));
        }
        return $ids;
    }

    /**
     * @return SummitType[]
     */
    public function summit_types()
    {
        return $this->belongsToMany('models\summit\SummitType','SummitEvent_AllowedSummitTypes', 'SummitEventID','SummitTypeID')->get();
    }

    /**
     * @param SummitType $summit_type
     */
    public function addSummitType(SummitType $summit_type)
    {
        $this->belongsToMany('models\summit\SummitType','SummitEvent_AllowedSummitTypes', 'SummitEventID','SummitTypeID')->attach($summit_type->ID);
    }

    public function clearSummitTypes()
    {
        $this->belongsToMany('models\summit\SummitType','SummitEvent_AllowedSummitTypes', 'SummitEventID','SummitTypeID')->detach();
    }

    /**
     * @return Company[]
     */
    public function sponsors()
    {
        return $this->belongsToMany('models\main\Company','SummitEvent_Sponsors','SummitEventID', 'CompanyID')->get();
    }

    /**
     * @return SummitEventType
     */
    public function getType()
    {
        return $this->hasOne('models\summit\SummitEventType', 'ID', 'TypeID')->first();
    }

    /**
     * @return Summit
     */
    public function getSummit()
    {
        return $this->hasOne('models\summit\Summit', 'ID', 'SummitID')->first();
    }

    /**
     * @return SummitAttendee[]
     */
    public function attendees()
    {
        return $this->belongsToMany('models\summit\SummitAttendee','SummitAttendee_Schedule','SummitEventID', 'SummitAttendeeID')
            ->where('IsCheckedIn','=',1)
            ->get();
    }

    public function toArray()
    {
        $values = parent::toArray();
        //check if description is empty, if so, set short description
        $description = $values['description'];
        if(empty($description))
        {
            $values['description'] = JsonUtils::toJsonString($this->ShortDescription);
        }

        $values['summit_types'] = $this->getSummitTypesIds();
        $values['sponsors']     = $this->getSponsorsIds();
        $tags = array();
        foreach($this->tags() as $t)
        {
            array_push($tags, $t->toArray());
        }
        $values['tags'] = $tags;
        return $values;
    }

    public function setFromAttendee()
    {
        $this->from_attendee = true;
        $this->array_mappings['IsCheckedIn'] = 'is_checked_in:json_boolean';
    }

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @param Order|null $order
     * @return array
     */
    public function feedback($page = 1, $per_page = 100, Filter $filter = null, Order $order = null)
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
            $class = 'models\\summit\\'.$e->ClassName;
            $entity = $class::find($e->ID);
            if(is_null($entity)) continue;
            array_push($feedback, $entity);
        }
        return array($total,$per_page, $current_page, $last_page, $feedback);
    }

    public function addFeedBack(SummitEventFeedback $feedback)
    {
        $this->hasMany('models\summit\SummitEventFeedback', 'EventID', 'ID')->where('ClassName','=','SummitEventFeedback')->save($feedback);
    }

    /**
     * @return Tag[]
     */
    public function tags()
    {
        return $this->belongsToMany('models\main\Tag','SummitEvent_Tags','SummitEventID','TagID')->get();
    }

    /**
     * @param string $tag
     */
    public function addTag($tag)
    {
        $t = Tag::where('Tag','=', trim($tag))->first();
        if(is_null($t))
        {
            $t = new Tag;
            $t->Tag = trim($tag);
            $t->save();
        }

        $this->belongsToMany('models\main\Tag','SummitEvent_Tags','SummitEventID','TagID')->attach($t->ID);
    }

    public function clearTags()
    {
        $this->belongsToMany('models\main\Tag','SummitEvent_Tags','SummitEventID','TagID')->detach();
    }

    public function publish()
    {
        if($this->Published)
            throw new ValidationException('Already published Summit Event');

        if(count($this->summit_types()) === 0)
            throw new EntityValidationException('To publish this event you must associate a valid summit type!');

        $start_date = $this->StartDate;
        $end_date   = $this->EndDate;

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

        $this->Published     = true;
        $this->PublishedDate = DateTimeUtils::nowRfc2822();
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return  (bool)$this->Published;
    }

    /**
     * @return void
     */
    public function unPublish()
    {
        $this->Published     = false;
        $this->PublishedDate = null;
    }

}