<?php namespace App\Models\Foundation\Summit\Factories;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionValueTemplate;

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




final class TrackQuestionValueTemplateFactory
{
    /**
     * @param array $data
     * @return TrackQuestionValueTemplate
     */
    public static function build(array $data){
        return self::populate(new TrackQuestionValueTemplate, $data);
    }

    /**
     * @param TrackQuestionValueTemplate $value
     * @param array $data
     * @return TrackQuestionValueTemplate
     */
    public static function populate(TrackQuestionValueTemplate $value, array $data){

        if(isset($data['value']))
            $value->setValue(trim($data['value']));

        if(isset($data['label']))
            $value->setLabel(trim($data['label']));

        return $value;
    }
}