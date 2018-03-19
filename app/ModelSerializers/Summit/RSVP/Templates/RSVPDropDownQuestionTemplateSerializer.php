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
use App\Models\Foundation\Main\CountryCodes;
use App\Models\Foundation\Summit\Events\RSVP\RSVPDropDownQuestionTemplate;
/**
 * Class RSVPDropDownQuestionTemplateSerializer
 * @package App\ModelSerializers\Summit\RSVP\Templates
 */
final class RSVPDropDownQuestionTemplateSerializer extends RSVPMultiValueQuestionTemplateSerializer
{
    protected static $array_mappings = [
        'CountrySelector' => 'is_country_selector:json_boolean',
        'UseChosenPlugin' => 'use_chosen_plugin:json_boolean',
        'Multiselect'     => 'is_multiselect:json_boolean',
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
        if(! $question instanceof RSVPDropDownQuestionTemplate) return [];
        $values  = parent::serialize($expand, $fields, $relations, $params);

        if($question->isCountrySelector()) {
            $question_values = [];
            $extra_options = [

                'Worldwide' => 'Worldwide',
                'Prefer not to say' => 'Prefer not to say',
                'Too many to list' => 'Too many to list',
            ];

            $options = array_merge($extra_options, CountryCodes::$iso_3166_countryCodes);
            foreach($options as $k => $v)
            {
                $question_values[] = [
                  'id'    => $k,
                  'value' => $v
                ];
            }
            $values['values'] = $question_values;
        }
        return $values;
    }
}