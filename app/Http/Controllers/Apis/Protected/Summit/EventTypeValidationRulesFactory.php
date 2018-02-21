<?php namespace App\Http\Controllers;
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
use App\Models\Foundation\Summit\Events\SummitEventTypeConstants;
use models\exceptions\ValidationException;
use models\summit\PresentationType;
/**
 * Class EventTypeValidationRulesFactory
 * @package App\Http\Controllers
 */
final class EventTypeValidationRulesFactory
{
    /**
     * @param array $data
     * @param boolean $update
     * @return array
     * @throws ValidationException
     */
    public static function build(array $data, $update = false){
        if (!isset($data['class_name']))
            throw new ValidationException("class_name parameter is mandatory");

        $class_name = trim($data['class_name']);

        if (!in_array($class_name, SummitEventTypeConstants::$valid_class_names)) {
            throw new ValidationException(
                sprintf
                (
                    "class_name param has an invalid value ( valid values are %s",
                    implode(", ", SummitEventTypeConstants::$valid_class_names)
                )
            );
        }

        $name_rule = 'sometimes|string';
        if(!$update) {

            $name_rule = 'required|string';
        }

        $base_rules = [
            'name'                   => $name_rule,
            'color'                  => 'sometimes|hex_color',
            'black_out_times'        => 'sometimes|boolean',
            'use_sponsors'           => 'sometimes|boolean',
            'are_sponsors_mandatory' => 'sometimes|boolean|required_with:use_sponsors',
            'allows_attachment'      => 'sometimes|boolean',
        ];

        $specific_rules = [];

        switch ($class_name){
            case PresentationType::ClassName:
            {
                $specific_rules = [
                    'use_speakers'               => 'sometimes|boolean',
                    'are_speakers_mandatory'     => 'sometimes|boolean|required_with:use_speakers',
                    'min_speakers'               => 'sometimes|integer|required_with:use_speakers',
                    'max_speakers'               => 'sometimes|integer|required_with:use_speakers|greater_than_field:min_speakers',
                    'use_moderator'              => 'sometimes|boolean',
                    'is_moderator_mandatory'     => 'sometimes|boolean|required_with:use_moderator',
                    'min_moderators'             => 'sometimes|integer|required_with:use_moderator',
                    'max_moderators'             => 'sometimes|integer|required_with:use_moderator|greater_than_field:min_moderators',
                    'should_be_available_on_cfp' => 'sometimes|boolean',
                    'moderator_label'            => 'sometimes|string'
                ];
            }
            break;

        }
        return array_merge($base_rules, $specific_rules);
    }
}