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

use models\utils\SilverstripeBaseModel;

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
        'ID'          => 'id',
        'Title'       => 'title:json_string',
        'Description' => 'description:json_string',
        'StartDate'   => 'start_date:datetime_epoch',
        'EndDate'     => 'end_date:datetime_epoch',
        'LocationID'  => 'location_id',
        'TypeID'      => 'type_id',
        'ClassName'   => 'class_name',
        'AllowFeedBack' => 'allow_feedback:json_boolean',
    );

    public function getSummitTypesIds()
    {
        $ids = array();
        foreach($this->summit_types() as $type)
        {
            array_push($ids, $type->ID);
        }
        return $ids;
    }

    public function getSponsorsIds()
    {
        $ids = array();
        foreach($this->sponsors() as $company)
        {
            array_push($ids, $company->ID);
        }
        return $ids;
    }

    public function summit_types()
    {
        return $this->belongsToMany('models\summit\SummitType','SummitEvent_AllowedSummitTypes', 'SummitEventID','SummitTypeID')->get();
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
        return $values;
    }

    public function setFromAttendee()
    {
        $this->from_attendee = true;
        $this->array_mappings['IsCheckedIn'] = 'is_checked_in:json_boolean';
    }

    /**
     * @return SummitEventFeedback[]
     */
    public function feedback()
    {
        return $this->hasMany('models\summit\SummitEventFeedback', 'EventID', 'ID')->where('Approved','=', 1)->get();
    }

    public function addFeedBack(SummitEventFeedback $feedback)
    {
        $this->hasMany('models\summit\SummitEventFeedback', 'EventID', 'ID')->save($feedback);
    }
}