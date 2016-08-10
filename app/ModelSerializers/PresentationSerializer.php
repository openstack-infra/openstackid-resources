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
/**
 * Class PresentationSerializer
 * @package ModelSerializers
 */
class PresentationSerializer extends SummitEventSerializer
{
    protected static $array_mappings = array
    (
        'Level'                   => 'level',
        'CategoryId'              => 'track_id:json_int',
        'ModeratorId'             => 'moderator_speaker_id:json_int',
        'ProblemAddressed'        => 'problem_addressed:json_string',
        'AttendeesExpectedLearnt' => 'attendees_expected_learnt:json_string',
    );

    protected static $allowed_fields = array
    (
        'track_id',
        'moderator_speaker_id',
        'level',
        'problem_addressed',
        'attendees_expected_learnt',
    );

    protected static $allowed_relations = array
    (
        'slides',
        'videos',
        'speakers',
        'links',
    );

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

        $values = parent::serialize($expand, $fields, $relations, $params);

        $presentation = $this->object;

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
            $videos = array();
            foreach ($presentation->getVideos() as $video) {
                $video_values   = SerializerRegistry::getInstance()->getSerializer($video)->serialize();
                if(empty($video_values['youtube_id'])) continue;
                $videos[] = $video_values;
            }
            $values['videos'] = $videos;
        }

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'speakers': {
                        $speakers = array();
                        foreach ($presentation->getSpeakers() as $s) {
                            $speakers[] = SerializerRegistry::getInstance()->getSerializer($s)->serialize();
                        }
                        $values['speakers'] = $speakers;
                    }
                    break;
                }
            }
        }
        return $values;
    }
}
