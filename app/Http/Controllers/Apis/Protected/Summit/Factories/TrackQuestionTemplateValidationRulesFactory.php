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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackCheckBoxListQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackCheckBoxQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackDropDownQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackLiteralContentQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplateConstants;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackRadioButtonListQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackTextBoxQuestionTemplate;
use models\exceptions\ValidationException;
/**
 * Class TrackQuestionTemplateValidationRulesFactory
 * @package App\Http\Controllers
 */
final class TrackQuestionTemplateValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     * @throws ValidationException
     */
    public static function build(array $data, $update = false)
    {

        if (!isset($data['class_name']))
            throw new ValidationException('class_name is required');

        $base_rules = [
            'class_name' => sprintf('required|in:%s', implode(",", TrackQuestionTemplateConstants::$valid_class_names))
        ];

        if ($update) {
            $base_rules = array_merge($base_rules, [
                'name' => 'sometimes|alpha_dash|max:255',
                'label' => 'sometimes|string',
                'is_mandatory' => 'sometimes|boolean',
                'is_read_only' => 'sometimes|boolean',
                'tracks' => 'sometimes|int_array',
            ]);
        } else {
            $base_rules = array_merge($base_rules, [
                'name' => 'required|alpha_dash|max:255',
                'label' => 'required|string',
                'is_mandatory' => 'sometimes|boolean',
                'is_read_only' => 'sometimes|boolean',
                'tracks' => 'sometimes|int_array',
            ]);
        }

        switch ($data['class_name']) {
            case TrackTextBoxQuestionTemplate::ClassName:
                {
                    return array_merge($base_rules, ['initial_value' => 'string|sometimes']);
                }
                break;
            case TrackCheckBoxQuestionTemplate::ClassName:
                {
                    return array_merge($base_rules, ['initial_value' => 'string|sometimes']);
                }
                break;
            case TrackCheckBoxListQuestionTemplate::ClassName:
                {
                    return array_merge
                    (
                        $base_rules,
                        TrackMultiValueQuestionTemplateValidationRulesFactory::build($data, $update)
                    );
                }
                break;
            case TrackRadioButtonListQuestionTemplate::ClassName:
                {
                    return array_merge
                    (
                        $base_rules,
                        TrackMultiValueQuestionTemplateValidationRulesFactory::build($data, $update)
                    );
                }
                break;
            case TrackDropDownQuestionTemplate::ClassName:
                {
                    return array_merge
                    (
                        $base_rules,
                        TrackMultiValueQuestionTemplateValidationRulesFactory::build($data, $update),
                        [
                            'is_multiselect' => 'sometimes|boolean',
                            'is_country_selector' => 'sometimes|boolean',
                        ]
                    );
                }
                break;
            case TrackLiteralContentQuestionTemplate::ClassName:
                {
                    return array_merge(
                        $base_rules,
                        TrackLiteralContentQuestionTemplateValidationRulesFactory::build($data, $update)
                    );
                }
                break;
            default:
                {
                    throw new ValidationException(sprintf('invalid class_name param (%s)', implode(",", TrackQuestionTemplateConstants::$valid_class_names)));
                }
                break;
        }

        return [];
    }
}