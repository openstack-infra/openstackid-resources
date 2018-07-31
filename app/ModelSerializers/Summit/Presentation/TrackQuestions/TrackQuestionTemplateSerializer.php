<?php namespace App\ModelSerializers\Summit\Presentation\TrackQuestions;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class TrackQuestionTemplateSerializer
 * @package App\ModelSerializers\Summit\Presentation\TrackQuestions
 */
class TrackQuestionTemplateSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Name'          => 'name:json_string',
        'Label'         => 'label:json_string',
        'Mandatory'     => 'is_mandatory:json_boolean',
        'ReadOnly'      => 'is_read_only:json_boolean',
        'AfterQuestion' => 'after_question:json_string',
        'ClassName'     => 'class_name:json_string',
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
        $values = parent::serialize($expand, $fields, $relations, $params);
        $question_template = $this->object;
        if(!$question_template instanceof TrackQuestionTemplate) return $values;

        $tracks = [];

        foreach($question_template->getTracks() as $t)
        {
            if(!is_null($expand) &&  in_array('tracks', explode(',',$expand))){
                $tracks[] = SerializerRegistry::getInstance()->getSerializer($t)->serialize();
            }
            else
                $tracks[] = intval($t->getId());
        }

        $values['tracks'] = $tracks;
        return $values;
    }
}