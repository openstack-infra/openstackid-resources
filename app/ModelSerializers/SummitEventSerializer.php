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
use libs\utils\JsonUtils;
use models\summit\SummitEvent;

/**
 * Class SummitEventSerializer
 * @package ModelSerializers
 */
class SummitEventSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Title'            => 'title:json_string',
        'Abstract'         => 'description:json_string',
        'SocialSummary'    => 'social_description:json_string',
        'StartDate'        => 'start_date:datetime_epoch',
        'EndDate'          => 'end_date:datetime_epoch',
        'LocationId'       => 'location_id:json_int',
        'SummitId'         => 'summit_id:json_int',
        'TypeId'           => 'type_id:json_int',
        'ClassName'        => 'class_name',
        'AllowFeedBack'    => 'allow_feedback:json_boolean',
        'AvgFeedbackRate'  => 'avg_feedback_rate:json_float',
        'Published'        => 'is_published:json_boolean',
        'HeadCount'        => 'head_count:json_int',
        'RSVPLink'         => 'rsvp_link:json_string',
        'IssExternalRSVP'  => 'rsvp_external:json_boolean',
        'CategoryId'       => 'track_id:json_int',
    );

    protected static $allowed_fields = array
    (
        'id',
        'title',
        'description',
        'social_description',
        'start_date',
        'end_date',
        'location_id',
        'summit_id',
        'type_id',
        'class_name',
        'allow_feedback',
        'avg_feedback_rate',
        'is_published',
        'head_count',
        'rsvp_link',
        'rsvp_external',
        'track_id',
    );

    protected static $allowed_relations = array
    (
        'sponsors',
        'tags',
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
        $event  = $this->object;
        if(!$event instanceof SummitEvent) return [];

        if(!count($relations)) $relations = $this->getAllowedRelations();

        $values = parent::serialize($expand, $fields, $relations, $params);

        if(in_array('sponsors', $relations))
            $values['sponsors'] = $event->getSponsorsIds();

        if(in_array('tags', $relations))
        {
            $tags = array();
            foreach ($event->getTags() as $tag) {
                $tags[] = SerializerRegistry::getInstance()->getSerializer($tag)->serialize();
            }
            $values['tags'] = $tags;
        }

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'feedback': {
                        $feedback = array();
                        foreach ($event->getFeedback() as $f) {
                            $feedback[] = SerializerRegistry::getInstance()->getSerializer($f)->serialize();
                        }
                        $values['feedback'] = $feedback;
                    }
                    break;
                    case 'location': {
                        if($event->hasLocation()){
                            unset($values['location_id']);
                            $values['location'] = SerializerRegistry::getInstance()->getSerializer($event->getLocation())->serialize();
                        }
                    }
                    break;
                    case 'sponsors': {
                        $sponsors = array();
                        foreach ($event->getSponsors() as $s) {
                            $sponsors[] = SerializerRegistry::getInstance()->getSerializer($s)->serialize();
                        }
                        $values['sponsors'] = $sponsors;
                    }
                    break;
                    case 'track': {
                       unset($values['track_id']);
                       $values['track'] = SerializerRegistry::getInstance()->getSerializer($event->getCategory())->serialize($expand);
                    }
                    case 'type': {
                        unset($values['type_id']);
                        $values['type'] = SerializerRegistry::getInstance()->getSerializer($event->getType())->serialize($expand);
                    }
                    break;
                }
            }
        }

        if(in_array('metrics', $relations)){
            // show metrics snapshot
            $metrics = [];
            foreach($event->getMetricsSnapShots() as $snapshot){
                $metrics[] = SerializerRegistry::getInstance()->getSerializer($snapshot)->serialize();
            }
            $values['metrics'] = $metrics;
        }

        return $values;
    }
}