<?php namespace App\ModelSerializers\Summit\RSVP\Templates;
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
use App\Models\Foundation\Summit\Events\RSVP\RSVPMultiValueQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPQuestionTemplate;
use ModelSerializers\SerializerRegistry;

/**
 * Class RSVPMultiValueQuestionTemplateSerializer
 * @package App\ModelSerializers\Summit\RSVP\Templates
 */
class RSVPMultiValueQuestionTemplateSerializer extends RSVPQuestionTemplateSerializer
{
    protected static $array_mappings = [
        'EmptyString'=> 'empty_string:json_string',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [])
    {
        $question = $this->object;
        if(! $question instanceof RSVPMultiValueQuestionTemplate) return [];
        $values  = parent::serialize($expand, $fields, $relations, $params);

        $question_values           = [];
        foreach ($question->getValues() as $value){
            $question_values[] = SerializerRegistry::getInstance()->getSerializer($value)->serialize($expand, [], ['none']);
        }

        $values['values'] = $question_values;
        if($question->hasDefaultValue())
            $values['default_value'] = SerializerRegistry::getInstance()->getSerializer($question->getDefaultValue())->serialize($expand, [], ['none']);;

        return $values;
    }
}