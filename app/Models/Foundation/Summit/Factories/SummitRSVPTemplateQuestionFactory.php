<?php namespace App\Models\Foundation\Summit\Factories;
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
use App\Models\Foundation\Summit\Events\RSVP\RSVPMultiValueQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPRadioButtonListQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPSingleValueTemplateQuestion;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTextAreaQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPTextBoxQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPDropDownQuestionTemplate;
use models\exceptions\ValidationException;
/**
 * Class SummitRSVPTemplateQuestionFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitRSVPTemplateQuestionFactory
{
    /**
     * @param array $data
     * @return RSVPQuestionTemplate|null
     * @throws ValidationException
     */
    public static function build(array $data){
        if(!isset($data['class_name'])) throw new ValidationException("missing class_name param");
        $question = null;
        switch($data['class_name']){
            case RSVPMemberEmailQuestionTemplate::ClassName :{
                $question = self::populateRSVPSingleValueTemplateQuestion(new RSVPMemberEmailQuestionTemplate, $data);
            }
            break;
            case RSVPMemberFirstNameQuestionTemplate::ClassName :{
                $question = self::populateRSVPSingleValueTemplateQuestion(new RSVPMemberFirstNameQuestionTemplate, $data);
            }
            break;
            case RSVPMemberLastNameQuestionTemplate::ClassName :{
                $question = self::populateRSVPSingleValueTemplateQuestion(new RSVPMemberLastNameQuestionTemplate, $data);
            }
            break;
            case RSVPTextBoxQuestionTemplate::ClassName :{
                $question = self::populateRSVPSingleValueTemplateQuestion(new RSVPTextBoxQuestionTemplate, $data);
            }
            break;
            case RSVPTextAreaQuestionTemplate::ClassName :{
                $question = self::populateRSVPSingleValueTemplateQuestion(new RSVPTextAreaQuestionTemplate, $data);
            }
            break;
            case RSVPCheckBoxListQuestionTemplate::ClassName :{
                $question = self::populateRSVPMultiValueQuestionTemplate(new RSVPCheckBoxListQuestionTemplate, $data);
            }
            break;
            case RSVPRadioButtonListQuestionTemplate::ClassName :{
                $question = self::populateRSVPMultiValueQuestionTemplate(new RSVPRadioButtonListQuestionTemplate, $data);
            }
            break;
            case RSVPDropDownQuestionTemplate::ClassName :{
                $question = self::populateRSVPDropDownQuestionTemplate(new RSVPDropDownQuestionTemplate, $data);
            }
            break;
            case RSVPLiteralContentQuestionTemplate::ClassName :{
                $question = self::populateRSVPLiteralContentQuestionTemplate(new RSVPLiteralContentQuestionTemplate, $data);
            }
            break;
        }
        return $question;
    }

    /**
     * @param RSVPQuestionTemplate $question
     * @param array $data
     * @return RSVPQuestionTemplate
     */
    private static function populateRSVPQuestionTemplate(RSVPQuestionTemplate $question, array $data){

        if(isset($data['name']))
            $question->setName(trim($data['name']));

        if(isset($data['label']))
            $question->setLabel(trim($data['label']));

        if(isset($data['is_mandatory']))
            $question->setIsMandatory(boolval($data['is_mandatory']));

        if(isset($data['is_read_only']))
            $question->setIsReadOnly(boolval($data['is_read_only']));

        return $question;
    }

    /**
     * @param RSVPSingleValueTemplateQuestion $question
     * @param array $data
     * @return RSVPQuestionTemplate
     */
    private static function populateRSVPSingleValueTemplateQuestion(RSVPSingleValueTemplateQuestion $question, array $data){
        if(isset($data['initial_value']))
            $question->setInitialValue(trim($data['initial_value']));

        return self::populateRSVPQuestionTemplate($question, $data);
    }

    /**
     * @param RSVPMultiValueQuestionTemplate $question
     * @param array $data
     * @return RSVPQuestionTemplate
     */
    private static function populateRSVPMultiValueQuestionTemplate(RSVPMultiValueQuestionTemplate $question, array $data){

        if(isset($data['empty_string']))
            $question->setEmptyString(trim($data['empty_string']));

        return self::populateRSVPQuestionTemplate($question, $data);
    }

    /**
     * @param RSVPDropDownQuestionTemplate $question
     * @param array $data
     * @return RSVPQuestionTemplate
     */
    private static function populateRSVPDropDownQuestionTemplate(RSVPDropDownQuestionTemplate $question, array $data){

        if(isset($data['is_multiselect']))
            $question->setIsMultiselect(boolval($data['is_multiselect']));

        if(isset($data['is_country_selector']))
            $question->setIsCountrySelector(boolval($data['is_country_selector']));

        if(isset($data['use_chosen_plugin']))
            $question->setUseChosenPlugin(boolval($data['use_chosen_plugin']));

        return self::populateRSVPMultiValueQuestionTemplate($question, $data);
    }

    /**
     * @param RSVPLiteralContentQuestionTemplate $question
     * @param array $data
     * @return RSVPQuestionTemplate
     */
    private static function populateRSVPLiteralContentQuestionTemplate(RSVPLiteralContentQuestionTemplate $question, array $data){
        if(isset($data['content']))
            $question->setContent(trim($data['content']));
        return self::populateRSVPQuestionTemplate($question, $data);
    }

    /**
     * @param RSVPQuestionTemplate $question
     * @param array $data
     * @return RSVPQuestionTemplate
     */
    public static function populate(RSVPQuestionTemplate $question, array $data){

        if($question instanceof RSVPMemberEmailQuestionTemplate){
            return self::populateRSVPSingleValueTemplateQuestion($question, $data);
        }

        if($question instanceof RSVPMemberFirstNameQuestionTemplate){
            return self::populateRSVPSingleValueTemplateQuestion($question, $data);
        }

        if($question instanceof RSVPMemberLastNameQuestionTemplate){
            return self::populateRSVPSingleValueTemplateQuestion($question, $data);
        }

        if($question instanceof RSVPTextBoxQuestionTemplate){
            return self::populateRSVPSingleValueTemplateQuestion($question, $data);
        }

        if($question instanceof RSVPTextAreaQuestionTemplate){
            return self::populateRSVPSingleValueTemplateQuestion($question, $data);
        }

        if($question instanceof RSVPCheckBoxListQuestionTemplate){
            return self::populateRSVPMultiValueQuestionTemplate($question, $data);
        }

        if($question instanceof RSVPRadioButtonListQuestionTemplate){
            return self::populateRSVPMultiValueQuestionTemplate($question, $data);
        }

        if($question instanceof RSVPDropDownQuestionTemplate){
            return self::populateRSVPDropDownQuestionTemplate($question, $data);
        }

        if($question instanceof RSVPLiteralContentQuestionTemplate){
            return self::populateRSVPLiteralContentQuestionTemplate($question, $data);
        }

        return $question;
    }
}