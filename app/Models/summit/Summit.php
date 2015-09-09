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
        'ID'        => 'id',
        'Name'      => 'name',
        'StartDate' => 'start_date',
        'EndDate'   => 'end_date',
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
        $res = $this->hasMany('models\summit\SummitAbstractLocation', 'SummitID', 'ID')->get();
        $locations = array();
        $venues    = array();
        foreach($res as $l)
        {

            $class = 'models\\summit\\'.$l->ClassName;
            $entity = $class::find($l->ID);
            if($l->ClassName === 'SummitVenue')
            {
                $venues[$entity->ID] = $entity;
            }
            if($l->ClassName === 'SummitVenueRoom')
            {
                $venue_id = intval($entity->VenueID);
                $venue    =  $venues[$venue_id];
                $venue->addRoom($entity);
            }
            else
                array_push($locations, $entity);
        }
        return $locations;
    }

    public function event_types()
    {
        return $this->hasMany('models\summit\SummitEventType', 'SummitID', 'ID')->get();
    }

    public function summit_types()
    {
        return $this->hasMany('models\summit\SummitType', 'SummitID', 'ID')->get();
    }

    public function schedule()
    {
        $res = $this->hasMany('models\summit\SummitEvent', 'SummitID', 'ID')->where('Published','=','1')->get();
        $events = array();
        foreach($res as $e)
        {
            $class = 'models\\summit\\'.$e->ClassName;
            $entity = $class::find($e->ID);
            array_push($events, $entity);
        }
        return $events;
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

    public function speakers()
    {
        $summit_id = $this->ID;
        $rows =   DB::connection('ss')->select("SELECT DISTINCT S.* FROM PresentationSpeaker S
INNER JOIN Presentation_Speakers PS ON PS.PresentationSpeakerID = S.ID
INNER JOIN Presentation P ON P.ID = PS.PresentationID
INNER JOIN SummitEvent E ON E.ID = P.ID
WHERE S.SummitID = {$summit_id} AND E.SummitID = {$summit_id} AND E.Published = 1;");

        $speakers = array();
        foreach($rows as $row)
        {
            $instance = new PresentationSpeaker();
            $instance->setRawAttributes((array)$row, true);
            array_push($speakers, $instance);
        }
        return $speakers;
    }
}