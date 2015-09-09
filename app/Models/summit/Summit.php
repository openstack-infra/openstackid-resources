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

use models\main\Company;
use models\utils\IEntity;
use DB;
use models\utils\SilverstripeBaseModel;

/**
 * Class Summit
 * @package models\summit
 */
class Summit extends SilverstripeBaseModel implements IEntity
{
    protected $table = 'Summit';

    protected $array_mappings = array
    (
        'ID'        => 'id:json_int',
        'Name'      => 'name:json_string',
        'StartDate' => 'start_date:datetime_epoch',
        'EndDate'   => 'end_date:datetime_epoch',
    );

    protected $hidden = array
    (

    );

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return (int)$this->ID;
    }

    /**
     * @return SummitAbstractLocation[]
     */
    public function locations()
    {
        $res       = $this->hasMany('models\summit\SummitAbstractLocation', 'SummitID', 'ID')->get();
        $locations = array();
        foreach($res as $l)
        {

            $class = 'models\\summit\\'.$l->ClassName;
            $entity = $class::find($l->ID);
            array_push($locations, $entity);
        }
        return $locations;
    }

    /**
     * @return SummitEventType[]
     */
    public function event_types()
    {
        return $this->hasMany('models\summit\SummitEventType', 'SummitID', 'ID')->get();
    }

    /**
     * @return SummitType[]
     */
    public function summit_types()
    {
        return $this->hasMany('models\summit\SummitType', 'SummitID', 'ID')->get();
    }

    /**
     * @return SummitTicketType[]
     */
    public function ticket_types()
    {
        return $this->hasMany('models\summit\SummitTicketType', 'SummitID', 'ID')->get();
    }

    /***
     * @param int $page
     * @param int $per_page
     * @param array $filters
     * @return array
     */
    public function attendees($page = 1, $per_page = 100, array $filters = array())
    {
        $rel = $this->hasMany('models\summit\SummitAttendee', 'SummitID', 'ID');
        foreach($filters as $f)
        {
            $rel = $rel->where($f['field'], $f['op'], $f['value']);
        }
        $pagination_result = $rel->paginate($per_page);
        $total             = $pagination_result->total();
        $items             = $pagination_result->items();
        $per_page          = $pagination_result->perPage();
        $current_page      = $pagination_result->currentPage();
        $last_page         = $pagination_result->lastPage();

        return array($total,$per_page, $current_page, $last_page, $items);
    }

    /**
     * @param int $page
     * @param int $per_page
     * @param array $filters
     * @return array
     */
    public function speakers($page = 1, $per_page = 100, array $filters = array())
    {
        $summit_id = $this->ID;
        $offset    = ($page - 1) * $per_page;
        $total     = DB::connection('ss')->select("SELECT COUNT(DISTINCT S.ID) AS QTY FROM PresentationSpeaker S
INNER JOIN Presentation_Speakers PS ON PS.PresentationSpeakerID = S.ID
INNER JOIN Presentation P ON P.ID = PS.PresentationID
INNER JOIN SummitEvent E ON E.ID = P.ID
WHERE S.SummitID = {$summit_id} AND E.SummitID = {$summit_id} AND E.Published = 1;");
        $total     = intval($total[0]->QTY);

        $rows      = DB::connection('ss')->select("SELECT DISTINCT S.* FROM PresentationSpeaker S
INNER JOIN Presentation_Speakers PS ON PS.PresentationSpeakerID = S.ID
INNER JOIN Presentation P ON P.ID = PS.PresentationID
INNER JOIN SummitEvent E ON E.ID = P.ID
WHERE S.SummitID = {$summit_id} AND E.SummitID = {$summit_id} AND E.Published = 1 limit {$per_page} offset $offset;");

        $items = array();
        foreach($rows as $row)
        {
            $instance = new PresentationSpeaker();
            $instance->setRawAttributes((array)$row, true);
            array_push($items, $instance);
        }

        $last_page = (int) ceil($total / $per_page);;
        return array($total,$per_page, $page, $last_page, $items);
    }

    /**
     * @param int $member_id
     * @return SummitAttendee
     */
    public function getAttendeeByMemberId($member_id)
    {
        return $this->hasMany('models\summit\SummitAttendee', 'SummitID', 'ID')->where('MemberID','=',$member_id)->first();
    }

    /**
     * @param int $attendee_id
     * @return SummitAttendee
     */
    public function getAttendeeById($attendee_id)
    {
        return $this->hasMany('models\summit\SummitAttendee', 'SummitID', 'ID')->where('SummitAttendee.ID','=',$attendee_id)->first();
    }

    /**
     * @return SummitEvent[]
     */
    public function schedule()
    {
        $res = $this
            ->hasMany('models\summit\SummitEvent', 'SummitID', 'ID')->where('Published','=','1')
            ->orderBy('StartDate','asc')
            ->orderBy('EndDate','asc')
            ->get();
        $events = array();
        foreach($res as $e)
        {
            $class = 'models\\summit\\'.$e->ClassName;
            $entity = $class::find($e->ID);
            array_push($events, $entity);
        }
        return $events;
    }

    /**
     * @param int $event_id
     * @return null|SummitEvent
     */
    public function getScheduleEvent($event_id)
    {
        $e = $this->hasMany('models\summit\SummitEvent', 'SummitID', 'ID')
            ->where('SummitEvent.ID','=', intval($event_id))
            ->where('Published','=','1')
            ->first();
        if(is_null($e)) return null;
        $class = 'models\\summit\\'.$e->ClassName;
        return $class::find($e->ID);
    }

    public function presentation_categories()
    {
        return $this->hasMany('models\summit\PresentationCategory', 'SummitID', 'ID')->get();
    }

    public function sponsors()
    {
        $summit_id = $this->ID;
        $rows =   DB::connection('ss')->select("SELECT DISTINCT C.* FROM SummitEvent_Sponsors S
INNER JOIN SummitEvent E ON E.ID = S.SummitEventID AND E.SummitID = {$summit_id}
INNER JOIN Company C ON C.ID = S.CompanyID");

        $sponsors = array();
        foreach($rows as $row)
        {
            $instance = new Company;
            $instance->setRawAttributes((array)$row, true);
            array_push($sponsors, $instance);
        }
        return $sponsors;
    }

    /**
     * @param int $speaker_id
     * @return null|PresentationSpeaker
     */
    public function getSpeakerById($speaker_id)
    {
        return $this->hasMany('models\summit\PresentationSpeaker', 'SummitID', 'ID')->where('PresentationSpeaker.ID','=', intval($speaker_id))->first();
    }

    /**
     * @param int $member_id
     * @return null|PresentationSpeaker
     */
    public function getSpeakerByMemberId($member_id)
    {
        return $this->hasMany('models\summit\PresentationSpeaker', 'SummitID', 'ID')->where('PresentationSpeaker.MemberID','=', intval($member_id))->first();
    }

    /**
     * @param int|null $from_id
     * @param \DateTime|null $from_date
     * @return SummitEntityEvent[]
     */
    public function getEntityEvents($from_id = null, \DateTime $from_date = null)
    {
        $relation = $this->hasMany('models\summit\SummitEntityEvent', 'SummitID', 'ID');
        if(!is_null($from_id))
        {
            $relation = $relation->where('SummitEntityEvent.ID','>=', intval($from_id));
        }
        if(!is_null($from_date))
        {
            $relation = $relation->where('SummitEntityEvent.Created','>=', $from_date);
        }
        return $relation
            ->orderBy('Created','asc')
            ->get();
    }

    public function toArray()
    {
        $values = parent::toArray();
        $time_zone_list = timezone_identifiers_list();
        $time_zone_id   = $this->TimeZone;
        if(!empty($time_zone_id) && isset($time_zone_list[$time_zone_id])){
            $time_zone_name           = $time_zone_list[$time_zone_id];
            $time_zone                = new \DateTimeZone($time_zone_name);
            $time_zone_info           = $time_zone->getLocation();
            $time_zone_info['name']   = $time_zone->getName();
            $now                      = new \DateTime("now", $time_zone);
            $time_zone_info['offset'] = $time_zone->getOffset($now);
            $values['time_zone'] = $time_zone_info;
        }
        return $values;
    }
}