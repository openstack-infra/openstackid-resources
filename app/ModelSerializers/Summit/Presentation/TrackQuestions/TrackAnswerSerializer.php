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
use ModelSerializers\SilverStripeSerializer;
/**
 * Class TrackAnswerSerializer
 * @package App\ModelSerializers\Summit\Presentation\TrackQuestions
 */
final class TrackAnswerSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Value'        => 'value:json_string',
        'QuestionName' => 'question_name:json_string',
        'QuestionId'   => 'question_id:json_int',
    ];
}