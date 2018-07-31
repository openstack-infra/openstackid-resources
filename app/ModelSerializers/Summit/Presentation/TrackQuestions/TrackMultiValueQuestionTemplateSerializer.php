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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackMultiValueQuestionTemplate;
use ModelSerializers\SerializerRegistry;
/**
 * Class TrackMultiValueQuestionTemplateSerializer
 * @package App\ModelSerializers\Summit\Presentation\TrackQuestions
 */
class TrackMultiValueQuestionTemplateSerializer extends TrackQuestionTemplateSerializer
{
    protected static $array_mappings = [
        'EmptyString'   => 'empty_string:json_string',
        'DefaultValueId' => 'default_value_id:json_int',
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
        if(!$question_template instanceof TrackMultiValueQuestionTemplate) return $values;

        $list = [];

        foreach($question_template->getValues() as $v)
        {
            $list[] = SerializerRegistry::getInstance()->getSerializer($v)->serialize();
        }

        $values['values'] = $list;
        return $values;
    }
}