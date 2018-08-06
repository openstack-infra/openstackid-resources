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
use models\summit\PresentationCategory;
/**
 * Class PresentationCategorySerializer
 * @package ModelSerializers
 */
final class PresentationCategorySerializer extends SilverStripeSerializer
{
    protected static $array_mappings =
    [
        'Title'                   => 'name:json_string',
        'Description'             => 'description:json_string',
        'Code'                    => 'code:json_string',
        'Slug'                    => 'slug:json_string',
        'SessionCount'            => 'session_count:json_int',
        'AlternateCount'          => 'alternate_count:json_int',
        'LightningCount'          => 'lightning_count:json_int',
        'LightningAlternateCount' => 'lightning_alternate_count:json_int',
        'VotingVisible'           => 'voting_visible:json_boolean',
        'ChairVisible'            => 'chair_visible:json_boolean',
        'SummitId'                => 'summit_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [] )
    {
        $category = $this->object;
        if(!$category instanceof PresentationCategory) return [];
        $values      = parent::serialize($expand, $fields, $relations, $params);
        $groups      = [];
        $allowed_tag = [];
        $extra_questions = [];
        $summit = $category->getSummit();

        foreach($category->getGroups() as $group){
            $groups[] = intval($group->getId());
        }

        foreach($category->getAllowedTags() as $tag){
            $allowed_tag[] = $tag->getId();
        }

        foreach($category->getExtraQuestions() as $question){
            $extra_questions[] = intval($question->getId());
        }

        $values['track_groups'] = $groups;
        $values['allowed_tag']  = $allowed_tag;
        $values['extra_questions'] = $extra_questions;

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'track_groups': {
                        $groups = [];
                        unset($values['track_groups']);
                        foreach ($category->getGroups() as $g) {
                            $groups[] = SerializerRegistry::getInstance()->getSerializer($g)->serialize(null, [], ['none']);
                        }
                        $values['track_groups'] = $groups;
                    }
                    break;
                }
                switch (trim($relation)) {
                    case 'allowed_tags': {
                        $allowed_tags = [];
                        unset($values['allowed_tags']);
                        foreach ($category->getAllowedTags() as $tag) {
                            $allowed_tag = SerializerRegistry::getInstance()->getSerializer($tag)->serialize(null, [], ['none']);
                            $track_tag_group = $summit->getTrackTagGroupForTag($tag);
                            if(!is_null($track_tag_group)){
                                $allowed_tag['track_tag_group'] = SerializerRegistry::getInstance()->getSerializer($track_tag_group)->serialize(null, [], ['none']);
                            }
                            $allowed_tags[] = $allowed_tag;
                        }
                        $values['allowed_tags'] = $allowed_tags;
                    }
                    break;
                }
                switch (trim($relation)) {
                    case 'extra_questions': {
                        $extra_questions = [];
                        unset($values['extra_questions']);
                        foreach ($category->getExtraQuestions() as $question) {
                            $extra_questions[] = SerializerRegistry::getInstance()->getSerializer($question)->serialize(null, [], ['none']);
                        }
                        $values['extra_questions'] = $extra_questions;
                    }
                    break;
                }
            }
        }

        return $values;
    }
}