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
use Filter;

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

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @return array
     */
    public function attendees($page = 1, $per_page = 100, Filter $filter = null)
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
     * @param Filter|null $filter
     * @return array
     */
    public function schedule($page = 1, $per_page = 100, Filter $filter = null)
    {
        $rel = $this->hasMany('models\summit\SummitEvent', 'SummitID', 'ID')->where('Published','=','1');

        if(!is_null($filter))
        {
            $filter->apply2Relation($rel, array
            (
                'title'      => 'SummitEvent.Title',
                'start_date' => 'SummitEvent.StartDate:datetime_epoch',
                'end_date'   => 'SummitEvent.EndDate:datetime_epoch',
            ));
        }

        $rel = $rel->orderBy('StartDate','asc')->orderBy('EndDate','asc');

        $pagination_result = $rel->paginate($per_page);
        $total             = $pagination_result->total();
        $items             = $pagination_result->items();
        $per_page          = $pagination_result->perPage();
        $current_page      = $pagination_result->currentPage();
        $last_page         = $pagination_result->lastPage();
        $events = array();
        foreach($items as $e)
        {
            $class = 'models\\summit\\'.$e->ClassName;
            $entity = $class::find($e->ID);
            array_push($events, $entity);
        }
        return array($total,$per_page, $current_page, $last_page, $events);
    }

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @return array
     */
    public function speakers($page = 1, $per_page = 100, Filter $filter = null)
    {
        $summit_id  = $this->ID;
        $offset     = ($page - 1) * $per_page;
        $str_filter = '';
        $bindings   = array();

        if(!is_null($filter))
        {
            $str_filter = ' AND '.$filter->toRawSQL(array
                (
                    'first_name' => 'M.FirstName',
                    'last_name'  => 'M.Surname',
                    'email'      => 'M.Email',
                ));
            $bindings = $filter->getSQLBindings();
        }

        $total     = DB::connection('ss')->select("SELECT COUNT(DISTINCT S.ID) AS QTY FROM PresentationSpeaker S
INNER JOIN Presentation_Speakers PS ON PS.PresentationSpeakerID = S.ID
INNER JOIN Presentation P ON P.ID = PS.PresentationID
INNER JOIN SummitEvent E ON E.ID = P.ID
INNER JOIN Member M ON M.ID = S.MemberID
WHERE S.SummitID = {$summit_id} AND E.SummitID = {$summit_id} AND E.Published = 1 {$str_filter} ;", $bindings);
        $total     = intval($total[0]->QTY);

        $rows      = DB::connection('ss')->select("
SELECT DISTINCT S.* FROM PresentationSpeaker S
INNER JOIN Presentation_Speakers PS ON PS.PresentationSpeakerID = S.ID
INNER JOIN Presentation P ON P.ID = PS.PresentationID
INNER JOIN SummitEvent E ON E.ID = P.ID
INNER JOIN Member M ON M.ID = S.MemberID
WHERE S.SummitID = {$summit_id} AND E.SummitID = {$summit_id} AND E.Published = 1 {$str_filter} limit {$per_page} offset $offset;", $bindings);

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

        if(!empty($time_zone_id) && isset($time_zone_list[$time_zone_id]))
        {

            $time_zone_name           = $time_zone_list[$time_zone_id];
            $time_zone                = new \DateTimeZone($time_zone_name);
            $time_zone_info           = $time_zone->getLocation();
            $time_zone_info['name']   = $time_zone->getName();
            $now                      = new \DateTime("now", $time_zone);
            $time_zone_info['offset'] = $time_zone->getOffset($now);
            $values['time_zone']      = $time_zone_info;
        }

        return $values;
    }
}