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
use App\Models\Foundation\Summit\Events\RSVP\RSVPCheckBoxListQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPLiteralContentQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPMemberEmailQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPMemberFirstNameQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPMemberLastNameQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPRadioButtonListQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTextAreaQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTextBoxQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPDropDownQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\Templates\SummitRSVPTemplateQuestionConstants;
use models\exceptions\ValidationException;
/**
 * Class SummitRSVPTemplateQuestionValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitRSVPTemplateQuestionValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     * @throws ValidationException
     */
    public static function build(array $data, $update = false){

        if(!isset($data['class_name']))
            throw new ValidationException('class_name is required');

        $base_rules = [
            'class_name' => sprintf('required|in:%s',  implode(",", SummitRSVPTemplateQuestionConstants::$valid_class_names))
        ];

        if($update){
            $base_rules = array_merge($base_rules, [
                'name'          => 'sometimes|alpha_dash|max:255',
                'label'         => 'sometimes|string',
                'is_mandatory'  => 'sometimes|boolean',
                'order'         => 'sometimes|int|min:1',
                'is_read_only;' => 'sometimes|boolean',
            ]);
        }
        else
        {
           $base_rules = array_merge($base_rules, [
               'name'          => 'required|alpha_dash|max:255',
               'label'         => 'required|string',
               'is_mandatory'  => 'sometimes|boolean',
               'is_read_only;' => 'sometimes|boolean',
           ]);
        }

        switch($data['class_name']){
            case RSVPMemberEmailQuestionTemplate::ClassName: {
               return $base_rules;
            }
                break;
            case RSVPMemberFirstNameQuestionTemplate::ClassName: {
                return $base_rules;
            }
            break;
            case RSVPMemberLastNameQuestionTemplate::ClassName: {
                return $base_rules;
            }
            break;
            case RSVPTextBoxQuestionTemplate::ClassName: {
                return array_merge($base_rules, ['initial_value' => 'string|sometimes']);
            }
            break;
            case RSVPTextAreaQuestionTemplate::ClassName: {
                return array_merge($base_rules, ['initial_value' => 'string|sometimes']);
            }
            break;
            case RSVPCheckBoxListQuestionTemplate::ClassName: {
                return array_merge($base_rules, SummitRSVPMultiValueQuestionTemplateValidationRulesFactory::build($data, $update));
            }
              break;
            case RSVPRadioButtonListQuestionTemplate::ClassName: {
                return array_merge($base_rules, SummitRSVPMultiValueQuestionTemplateValidationRulesFactory::build($data, $update));
            }
            break;
            case RSVPDropDownQuestionTemplate::ClassName: {
                return array_merge
                (
                    $base_rules,
                    SummitRSVPMultiValueQuestionTemplateValidationRulesFactory::build($data, $update),
                    [
                        'is_multiselect'      => 'sometimes|boolean',
                        'is_country_selector' => 'sometimes|boolean',
                        'use_chosen_plugin'   => 'sometimes|boolean',
                    ]
                );
            }
            break;
            case RSVPLiteralContentQuestionTemplate::ClassName: {
                return array_merge($base_rules, SummitRSVPLiteralContentQuestionTemplateValidationRulesFactory::build($data, $update));
            }
            break;
            default:{
                throw new ValidationException(sprintf('invalid class_name param (%s)', implode(",", SummitRSVPTemplateQuestionConstants::$valid_class_names)));
            }
            break;
        }

        return [];
    }
}