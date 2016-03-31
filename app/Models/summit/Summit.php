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

use DB;
use Config;
use models\main\Company;
use models\main\Image;
use models\utils\SilverstripeBaseModel;
use utils\ExistsFilterManyManyMapping;
use utils\ExistsFilterManyToOneMapping;
use utils\Filter;
use utils\Order;

/**
 * Class Summit
 * @package models\summit
 */
class Summit extends SilverstripeBaseModel
{
    protected $table = 'Summit';

    protected $array_mappings = array
    (
        'ID'                     => 'id:json_int',
        'Name'                   => 'name:json_string',
        'SummitBeginDate'        => 'start_date:datetime_epoch',
        'SummitEndDate'          => 'end_date:datetime_epoch',
        'StartShowingVenuesDate' => 'start_showing_venues_date:datetime_epoch',
        'Active'                 => 'active:json_boolean',
    );

    protected $hidden = array
    (

    );

    /**
     * @return SummitAbstractLocation[]
     */
    public function locations()
    {
        $res       = $this->hasMany('models\summit\SummitAbstractLocation', 'SummitID', 'ID')->get();
        $locations = array();
        foreach($res as $l)
        {

            $class  = 'models\\summit\\'.$l->ClassName;
            $entity = $class::find($l->ID);
            array_push($locations, $entity);
        }
        return $locations;
    }

    /**
     * @return Image
     */
    public function logo()
    {
        return $this->hasOne('models\main\Image', 'ID', 'LogoID')->first();
    }

    /**
     * @param int $location_id
     * @return SummitAbstractLocation
     */
    public function getLocation($location_id)
    {
        $location = $this->hasMany('models\summit\SummitAbstractLocation', 'SummitID', 'ID')->where('SummitAbstractLocation.ID', '=', $location_id)->get()->first();
        if(!is_null($location))
        {
            $class    = 'models\\summit\\'.$location->ClassName;
            $location = $class::find($location->ID);
        }
        return $location;
    }

    /**
     * @return SummitEventType[]
     */
    public function event_types()
    {
        return $this->hasMany('models\summit\SummitEventType', 'SummitID', 'ID')->get();
    }

    /**
     * @param int $event_type_id
     * @return SummitEventType
     */
    public function getEventType($event_type_id)
    {
        return $this->hasMany('models\summit\SummitEventType', 'SummitID', 'ID')->where('ID','=', intval($event_type_id))->first();
    }

    /**
     * @return SummitType[]
     */
    public function summit_types()
    {
        return $this->hasMany('models\summit\SummitType', 'SummitID', 'ID')->get();
    }

    /**
     * @param int $summit_type_id
     * @return SummitType
     */
    public function getSummitType($summit_type_id)
    {
        return $this->hasMany('models\summit\SummitType', 'SummitID', 'ID')->where('ID','=', intval($summit_type_id))->first();
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
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @return array
     */
    public function schedule($page = 1, $per_page = 100, Filter $filter = null)
    {
        return $this->events($page, $per_page, $filter, true);
    }

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter|null $filter
     * @param bool|false $published
     * @return array
     */
    public function events($page = 1, $per_page = 100, Filter $filter = null, $published = false)
    {
        $rel = $this->hasMany('models\summit\SummitEvent', 'SummitID', 'ID');
        if($published)
        {
            $rel = $rel->where('Published','=','1');
        }

        if(!is_null($filter))
        {
            $filter->apply2Relation($rel, array
            (
                'title'         => 'SummitEvent.Title',
                'start_date'    => 'SummitEvent.StartDate:datetime_epoch',
                'end_date'      => 'SummitEvent.EndDate:datetime_epoch',
                'tags'          => new ExistsFilterManyManyMapping
                (
                    'Tag',
                    'SummitEvent_Tags',
                    'SummitEvent_Tags.TagID = Tag.ID',
                    "SummitEvent_Tags.SummitEventID = SummitEvent.ID AND Tag.Tag :operator ':value'"
                ),
                'summit_type_id'=> new ExistsFilterManyManyMapping
                (
                    'SummitType',
                    'SummitEvent_AllowedSummitTypes',
                    'SummitType.ID = SummitEvent_AllowedSummitTypes.SummitTypeID',
                    'SummitEvent_AllowedSummitTypes.SummitEventID = SummitEvent.ID AND SummitType.ID :operator :value'
                ),
                'event_type_id' => new ExistsFilterManyToOneMapping
                (
                    'SummitEventType',
                    'SummitEventType.ID = SummitEvent.TypeID AND SummitEventType.ID :operator :value'
                ),
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
            if(is_null($entity)) continue;
            array_push($events, $entity);
        }
        return array($total,$per_page, $current_page, $last_page, $events);
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

    /**
     * @param int $event_id
     * @return null|SummitEvent
     */
    public function getEvent($event_id)
    {
        $e = $this->hasMany('models\summit\SummitEvent', 'SummitID', 'ID')
            ->where('SummitEvent.ID','=', intval($event_id))
            ->first();
        if(is_null($e)) return null;
        $class = 'models\\summit\\'.$e->ClassName;
        return $class::find($e->ID);
    }

    /**
     * @return PresentationCategory[]
     */
    public function presentation_categories()
    {
        return $this->hasMany('models\summit\PresentationCategory', 'SummitID', 'ID')->get();
    }

    /**
     * @return PresentationCategoryGroup[]
     */
    public function category_groups()
    {
        return $this->hasMany('models\summit\PresentationCategoryGroup', 'SummitID', 'ID')->get();
    }


    /**
     * @param int $group_id
     * @return null|PresentationCategoryGroup
     */
    public function getCategoryGroup($group_id)
    {
        return $this->hasMany('models\summit\PresentationCategoryGroup', 'SummitID', 'ID')
            ->where('PresentationCategoryGroup.ID','=', intval($group_id))
            ->first();
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
        return  PresentationSpeaker::where('PresentationSpeaker.ID','=', intval($speaker_id))
            ->whereRaw(" EXISTS (
           SELECT 1 FROM Presentation_Speakers INNER JOIN SummitEvent
            ON
            SummitEvent.ID = Presentation_Speakers.PresentationID
            WHERE
            Presentation_Speakers.PresentationSpeakerID = PresentationSpeaker.ID
            AND SummitEvent.SummitID =  {$this->ID}) ")
            ->first();
    }

    /**
     * @param int $member_id
     * @return null|PresentationSpeaker
     */
    public function getSpeakerByMemberId($member_id)
    {

        return  PresentationSpeaker::where('PresentationSpeaker.MemberID','=', intval($member_id))
            ->whereRaw(" EXISTS (
           SELECT 1 FROM Presentation_Speakers INNER JOIN SummitEvent
            ON
            SummitEvent.ID = Presentation_Speakers.PresentationID
            WHERE
            Presentation_Speakers.PresentationSpeakerID = PresentationSpeaker.ID
            AND SummitEvent.SummitID =  {$this->ID}) ")
            ->first();
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
            $relation = $relation->where('SummitEntityEvent.ID','>', intval($from_id));
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
        $time_zone_list      = timezone_identifiers_list();
        $time_zone_id        = $this->TimeZone;
        $values['time_zone'] = null;
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
        $values['logo']           = ($this->logo() !== null) ? Config::get("server.assets_base_url", 'https://www.openstack.org/'). $this->logo()->Filename : null;
        if(empty($values['name']))
        {
            $values['name'] = $this->Title;
        }
        return $values;
    }


    /**
     * @param $value
     * @return null|string
     */
    public function convertDateFromTimeZone2UTC($value)
    {
        $time_zone_id   = $this->TimeZone;
        if(empty($time_zone_id)) return $value;
        $time_zone_list = timezone_identifiers_list();

        if(isset($time_zone_list[$time_zone_id]) && !empty($value))
        {
            $utc_timezone      = new \DateTimeZone("UTC");
            $time_zone_name = $time_zone_list[$time_zone_id];
            $time_zone   = new \DateTimeZone($time_zone_name);
            $date  = new \DateTime($value, $time_zone);
            $date->setTimezone($utc_timezone);
            return $date->format("Y-m-d H:i:s");
        }
        return null;
    }

    /**
     * @param $value
     * @return null|string
     */
    public function convertDateFromUTC2TimeZone($value)
    {
        $time_zone_id   = $this->TimeZone;
        if(empty($time_zone_id)) return $value;
        $time_zone_list = timezone_identifiers_list();

        if(isset($time_zone_list[$time_zone_id]) && !empty($value))
        {
            $utc_timezone   = new \DateTimeZone("UTC");
            $time_zone_name = $time_zone_list[$time_zone_id];
            $time_zone      = new \DateTimeZone($time_zone_name);
            $date           = new \DateTime($value, $utc_timezone);

            $date->setTimezone($time_zone);
            return $date->format("Y-m-d H:i:s");
        }
        return null;
    }

    /**
     * @param SummitEvent $summit_event
     * @return bool
     */
    public function isEventInsideSummitDuration(SummitEvent $summit_event)
    {
        $event_start_date  = $summit_event->StartDate;
        $event_end_date    = $summit_event->EndDate;
        $summit_start_date = new \DateTime($this->convertDateFromUTC2TimeZone($this->SummitBeginDate));
        $summit_end_date   = new \DateTime($this->convertDateFromUTC2TimeZone($this->SummitEndDate));

        return  $event_start_date >= $summit_start_date && $event_start_date <= $summit_end_date &&
        $event_end_date <= $summit_end_date && $event_end_date >= $event_start_date;
    }

    /**
     * @return \DateTime
     */
    public function getLocalBeginDate()
    {
        return new \DateTime($this->convertDateFromUTC2TimeZone($this->SummitBeginDate));
    }

    /**
     * @return \DateTime
     */
    public function getLocalEndDate()
    {
        return new \DateTime($this->convertDateFromUTC2TimeZone($this->SummitEndDate));
    }

}