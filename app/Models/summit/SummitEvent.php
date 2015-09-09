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

use models\main\Tag;
use models\utils\SilverstripeBaseModel;
use utils\Filter;
use utils\Order;

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
        'ID'            => 'id:json_int',
        'Title'         => 'title:json_string',
        'Description'   => 'description:json_string',
        'StartDate'     => 'start_date:datetime_epoch',
        'EndDate'       => 'end_date:datetime_epoch',
        'LocationID'    => 'location_id:json_int',
        'TypeID'        => 'type_id:json_int',
        'ClassName'     => 'class_name',
        'AllowFeedBack' => 'allow_feedback:json_boolean',
    );

    public function getSummitTypesIds()
    {
        $ids = array();
        foreach($this->summit_types() as $type)
        {
            array_push($ids, intval($type->ID));
        }
        return $ids;
    }

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

    public function addSummitType(SummitType $summit_type)
    {
        $this->belongsToMany('models\summit\SummitType','SummitEvent_AllowedSummitTypes', 'SummitEventID','SummitTypeID')->attach($summit_type->ID);
    }

    public function clearSummitTypes()
    {
        $this->belongsToMany('models\summit\SummitType','SummitEvent_AllowedSummitTypes', 'SummitEventID','SummitTypeID')->detach();
    }

    public function sponsors()
    {
        return $this->belongsToMany('models\main\Company','SummitEvent_Sponsors','SummitEventID', 'CompanyID')->get();
    }

    public function attendees()
    {
        return $this->belongsToMany('models\summit\SummitAttendee','SummitAttendee_Schedule','SummitEventID', 'SummitAttendeeID')
            ->where('IsCheckedIn','=',1)
            ->get();
    }

    public function toArray()
    {
        $values = parent::toArray();
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

}