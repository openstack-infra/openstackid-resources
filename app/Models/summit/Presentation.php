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
        'ID'            => 'id:json_int',
        'Title'         => 'title:json_string',
        'Description'   => 'description:json_string',
        'StartDate'     => 'start_date:datetime_epoch',
        'EndDate'       => 'end_date:datetime_epoch',
        'LocationID'    => 'location_id:json_int',
        'TypeID'        => 'type_id:json_int',
        'ClassName'     => 'class_name',
        'CategoryID'    => 'track_id:json_int',
        'ModeratorID'   => 'moderator_speaker_id:json_int',
        'Level'         => 'level',
        'AllowFeedBack' => 'allow_feedback:json_boolean',
    );

    /**
     * @return PresentationSpeaker[]
     */
    public function speakers()
    {
        return $this->belongsToMany('models\summit\PresentationSpeaker','Presentation_Speakers','PresentationID','PresentationSpeakerID')->get();
    }

    /**
     * @return Tag[]
     */
    public function tags()
    {
        return $this->belongsToMany('models\main\Tag','Presentation_Tags','PresentationID','TagID')->get();
    }

    public function getSpeakerIds()
    {
        $ids = array();
        foreach($this->speakers() as $speaker)
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
     * @return array
     */
    public function toArray()
    {
        $values = parent::toArray();
        if(!$this->from_speaker)
        $values['speakers'] = $this->getSpeakerIds();

        $tags = array();
        foreach($this->tags() as $t)
        {
           array_push($tags, $t->toArray());
        }
        $values['tags'] = $tags;

        $slides = array();
        foreach($this->slides() as $s)
        {
            array_push($slides, $s->toArray());
        }
        $values['slides'] = $slides;

        $videos = array();
        foreach($this->videos() as $v)
        {
            array_push($videos, $v->toArray());
        }
        $values['videos'] = $videos;

        return $values;
    }

    /**
     * @return PresentationSpeakerFeedback[]
     */
    public function speakers_feedback()
    {
        return $this->hasMany('models\summit\PresentationSpeakerFeedback', 'EventID', 'ID')->where('Approved','=', 1)->get();
    }

    /**
     * @return PresentationVideo[]
     */
    public function videos()
    {
        return $this->hasMany('models\summit\PresentationVideo', 'PresentationID', 'ID')->get();
    }

    /**
     * @return PresentationSlide[]
     */
    public function slides()
    {
        return $this->hasMany('models\summit\PresentationSlide', 'PresentationID', 'ID')->get();
    }
}
