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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackCheckBoxListQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackCheckBoxQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackDropDownQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackLiteralContentQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackMultiValueQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackRadioButtonListQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackSingleValueTemplateQuestion;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackTextBoxQuestionTemplate;
use models\exceptions\ValidationException;
/**
 * Class TrackQuestionTemplateFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class TrackQuestionTemplateFactory
{
    /**
     * @param array $data
     * @return TrackQuestionTemplate|null
     * @throws ValidationException
     */
    public static function build(array $data)
    {
        if (!isset($data['class_name'])) throw new ValidationException("missing class_name param");
        $track_question_template = null;
        switch ($data['class_name']) {
            case TrackTextBoxQuestionTemplate::ClassName:
                {
                    $track_question_template = self::populateTrackSingleValueTemplateQuestion(new TrackTextBoxQuestionTemplate, $data);
                }
                break;
            case TrackCheckBoxQuestionTemplate::ClassName:
                {
                    $track_question_template = self::populateTrackSingleValueTemplateQuestion(new TrackCheckBoxQuestionTemplate(), $data);
                }
                break;
            case TrackCheckBoxListQuestionTemplate::ClassName :
                {
                    $track_question_template = self::populateTrackMultiValueQuestionTemplate(new TrackCheckBoxListQuestionTemplate, $data);
                }
                break;
            case TrackRadioButtonListQuestionTemplate::ClassName :
                {
                    $track_question_template = self::populateTrackMultiValueQuestionTemplate(new TrackRadioButtonListQuestionTemplate, $data);
                }
                break;
            case TrackDropDownQuestionTemplate::ClassName :
                {
                    $track_question_template = self::populateTrackDropDownQuestionTemplate(new TrackDropDownQuestionTemplate, $data);
                }
                break;
            case TrackLiteralContentQuestionTemplate::ClassName :
                {
                    $track_question_template = self::populateTrackLiteralContentQuestionTemplate(new TrackLiteralContentQuestionTemplate, $data);
                }
                break;
        }
        return $track_question_template;
    }

    /**
     * @param TrackSingleValueTemplateQuestion $question
     * @param array $data
     * @return TrackQuestionTemplate
     */
    private static function populateTrackSingleValueTemplateQuestion(TrackSingleValueTemplateQuestion $question, array $data)
    {
        if (isset($data['initial_value']))
            $question->setInitialValue(trim($data['initial_value']));

        return self::populateTrackQuestionTemplate($question, $data);
    }

    /**
     * @param TrackMultiValueQuestionTemplate $question
     * @param array $data
     * @return TrackQuestionTemplate
     */
    private static function populateTrackMultiValueQuestionTemplate(TrackMultiValueQuestionTemplate $question, array $data)
    {

        if (isset($data['empty_string']))
            $question->setEmptyString(trim($data['empty_string']));

        return self::populateTrackQuestionTemplate($question, $data);
    }

    /**
     * @param TrackQuestionTemplate $question
     * @param array $data
     * @return TrackQuestionTemplate
     */
    private static function populateTrackQuestionTemplate(TrackQuestionTemplate $question, array $data)
    {

        if (isset($data['name']))
            $question->setName(trim($data['name']));

        if (isset($data['label']))
            $question->setLabel(trim($data['label']));

        if (isset($data['is_mandatory']))
            $question->setIsMandatory(boolval($data['is_mandatory']));

        if (isset($data['is_read_only']))
            $question->setIsReadOnly(boolval($data['is_read_only']));

        return $question;
    }

    /**
     * @param TrackDropDownQuestionTemplate $question
     * @param array $data
     * @return TrackQuestionTemplate
     */
    private static function populateTrackDropDownQuestionTemplate(TrackDropDownQuestionTemplate $question, array $data)
    {

        if (isset($data['is_multiselect']))
            $question->setIsMultiselect(boolval($data['is_multiselect']));

        if (isset($data['is_country_selector']))
            $question->setIsCountrySelector(boolval($data['is_country_selector']));

        return self::populateTrackMultiValueQuestionTemplate($question, $data);
    }

    /**
     * @param TrackLiteralContentQuestionTemplate $question
     * @param array $data
     * @return TrackQuestionTemplate
     */
    private static function populateTrackLiteralContentQuestionTemplate(TrackLiteralContentQuestionTemplate $question, array $data)
    {
        if (isset($data['content']))
            $question->setContent(trim($data['content']));
        return self::populateTrackQuestionTemplate($question, $data);
    }

    /**
     * @param TrackQuestionTemplate $question
     * @param array $data
     * @return TrackQuestionTemplate
     */
    public static function populate(TrackQuestionTemplate $question, array $data){
        if($question instanceof TrackTextBoxQuestionTemplate){
            return self::populateTrackSingleValueTemplateQuestion($question, $data);
        }
        if($question instanceof TrackCheckBoxQuestionTemplate){
            return self::populateTrackSingleValueTemplateQuestion($question, $data);
        }
        if($question instanceof TrackCheckBoxListQuestionTemplate){
            return self::populateTrackMultiValueQuestionTemplate($question, $data);
        }

        if($question instanceof TrackRadioButtonListQuestionTemplate){
            return self::populateTrackMultiValueQuestionTemplate($question, $data);
        }

        if($question instanceof TrackDropDownQuestionTemplate){
            return self::populateTrackDropDownQuestionTemplate($question, $data);
        }

        if($question instanceof TrackLiteralContentQuestionTemplate){
            return self::populateTrackLiteralContentQuestionTemplate($question, $data);
        }

        return $question;
    }
}