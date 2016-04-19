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
use DB;

/**
 * Class Presentation
 * @package models\summit
 */
class Presentation extends SummitEvent
{
    protected $table = 'Presentation';

    protected $mtiClassType = 'concrete';

    /**
     * @var bool
     */
    private $from_speaker;

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
        'CategoryID'      => 'track_id:json_int',
        'ModeratorID'     => 'moderator_speaker_id:json_int',
        'Level'           => 'level',
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
        'track_id',
        'moderator_speaker_id',
        'level',
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
        'slides',
        'videos',
        'speakers',
    );


    /**
     * @param array $fields
     * @return PresentationSpeaker[]
     */
    public function speakers(array $fields = array('*'))
    {
        return $this->belongsToMany('models\summit\PresentationSpeaker','Presentation_Speakers','PresentationID','PresentationSpeakerID')->get($fields);
    }

    public function getSpeakerIds()
    {
        $ids = array();

        foreach($this->speakers(array('PresentationSpeaker.ID')) as $speaker)
        {
            array_push($ids, intval($speaker->ID));
        }

        return $ids;
    }

    public function setFromSpeaker()
    {
        $this->from_speaker = true;
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

        $values = parent::toArray($fields, $relations);

        if(in_array('speakers', $relations)) {
            if (!$this->from_speaker)
                $values['speakers'] = $this->getSpeakerIds();
        }

        if(in_array('slides', $relations))
        {
            $slides = array();
            foreach ($this->slides() as $s) {
                array_push($slides, $s->toArray());
            }
            $values['slides'] = $slides;
        }

        if(in_array('videos', $relations))
        {
            $videos = array();
            foreach ($this->videos() as $v) {
                array_push($videos, $v->toArray());
            }
            $values['videos'] = $videos;
        }

        return $values;
    }
    /**
     * @return PresentationVideo[]
     */
    public function videos()
    {
        $bindings = array('presentation_id' => $this->ID);
        $rows     = DB::connection('ss')->select("select * from `PresentationVideo` left join `PresentationMaterial` on `PresentationVideo`.`ID` = `PresentationMaterial`.`ID`
where `PresentationMaterial`.`PresentationID` = :presentation_id and `PresentationMaterial`.`PresentationID` is not null", $bindings);

        $videos = array();
        foreach($rows as $row)
        {
            $instance = new PresentationVideo;
            $instance->setRawAttributes((array)$row, true);
            array_push($videos, $instance);
        }
        return $videos;
    }

    /**
     * @return PresentationSlide[]
     */
    public function slides()
    {
        $bindings = array('presentation_id' => $this->ID);
        $rows     = DB::connection('ss')->select("select * from `PresentationSlide` left join `PresentationMaterial` on `PresentationSlide`.`ID` = `PresentationMaterial`.`ID`
where `PresentationMaterial`.`PresentationID` = :presentation_id and `PresentationMaterial`.`PresentationID` is not null", $bindings);

        $slides = array();
        foreach($rows as $row)
        {
            $instance = new PresentationSlide;
            $instance->setRawAttributes((array)$row, true);
            array_push($slides, $instance);
        }
        return $slides;
    }

    /**
     * @param SummitEvent $event
     * @return Presentation
     */
    public static function toPresentation(SummitEvent $event){
        $presentation  = new Presentation();
        $attributes    = $event->getAttributes();
        $presentation->setRawAttributes($attributes);
        return $presentation;
    }
}
