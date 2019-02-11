<?php namespace ModelSerializers;
/**
 * Copyright 2016 OpenStack Foundation
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
use models\summit\Presentation;
/**
 * Class PresentationSerializer
 * @package ModelSerializers
 */
class PresentationSerializer extends SummitEventSerializer
{
    protected static $array_mappings = [

        'Level'                   => 'level',
        'CreatorId'               => 'creator_id:json_int',
        'SelectionPlanId'         => 'selection_plan_id:json_int',
        'ProblemAddressed'        => 'problem_addressed:json_string',
        'AttendeesExpectedLearnt' => 'attendees_expected_learnt:json_string',
        'ToRecord'                => 'to_record:json_boolean',
        'AttendingMedia'          => 'attending_media:json_boolean',
        'StatusNice'              => 'status:json_string',
        'ProgressNice'            => 'progress:json_string',
    ];

    protected static $allowed_fields = [
        'track_id',
        'creator_id',
        'selection_plan_id',
        'level',
        'problem_addressed',
        'attendees_expected_learnt',
        'to_record',
        'attending_media',
        'status',
        'progress',
    ];

    protected static $allowed_relations = [
        'slides',
        'videos',
        'speakers',
        'links',
        'extra_questions',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        if(!count($relations)) $relations = $this->getAllowedRelations();

        $presentation = $this->object;

        if(!$presentation instanceof Presentation) return [];

        $values = parent::serialize($expand, $fields, $relations, $params);

        if(in_array('speakers', $relations)) {
            $values['speakers'] = $presentation->getSpeakerIds();
        }

        if(in_array('slides', $relations))
        {
            $slides = array();
            foreach ($presentation->getSlides() as $slide) {
                $slide_values  = SerializerRegistry::getInstance()->getSerializer($slide)->serialize();
                if(empty($slide_values['link'])) continue;
                $slides[] = $slide_values;
            }
            $values['slides'] = $slides;
        }

        if(in_array('links', $relations))
        {
            $links = array();
            foreach ($presentation->getLinks() as $link) {
                $link_values  = SerializerRegistry::getInstance()->getSerializer($link)->serialize();
                if(empty($link_values['link'])) continue;
                $links[] = $link_values;
            }
            $values['links'] = $links;
        }

        if(in_array('videos', $relations))
        {
            $videos = [];
            foreach ($presentation->getVideos() as $video) {
                $video_values   = SerializerRegistry::getInstance()->getSerializer($video)->serialize();
                if(empty($video_values['youtube_id'])) continue;
                $videos[] = $video_values;
            }
            $values['videos'] = $videos;
        }

        if(in_array('extra_questions', $relations))
        {
            $answers = [];
            foreach ($presentation->getAnswers() as $answer) {
                $answers[]= SerializerRegistry::getInstance()->getSerializer($answer)->serialize();
            }
            $values['extra_questions'] = $answers;
        }

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'speakers': {
                        $speakers = [];
                        foreach ($presentation->getSpeakers() as $presentation_speaker) {
                            $speakers[] = SerializerRegistry::getInstance()->getSerializer($presentation_speaker)->serialize();
                        }
                        $values['speakers'] = $speakers;
                        // legacy field, should be removed
                        $values['moderator'] = null;
                     }
                    case 'creator':{
                        if($presentation->getCreatorId() > 0) {
                            unset($values['creator_id']);
                            $values['creator'] = SerializerRegistry::getInstance()->getSerializer($presentation->getCreator())->serialize();
                        }
                    }
                    break;
                    case 'selection_plan':{
                        if($presentation->getSelectionPlanId() > 0) {
                            unset($values['selection_plan_id']);
                            $values['selection_plan'] = SerializerRegistry::getInstance()->getSerializer($presentation->getSelectionPlan())->serialize();
                        }
                    }
                    break;
                }
            }
        }
        return $values;
    }
}
