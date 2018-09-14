<?php namespace App\Services\Model;
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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionValueTemplate;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
/**
 * Interface ITrackQuestionTemplate
 * @package App\Services\Model
 */
interface ITrackQuestionTemplateService
{
    /**
     * @param array $payload
     * @return TrackQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackQuestionTemplate(array $payload);

    /**
     * @param int $track_question_template_id
     * @param array $payload
     * @return TrackQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackQuestionTemplate($track_question_template_id, array $payload);

    /**
     * @param int $track_question_template_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function deleteTrackQuestionTemplate($track_question_template_id);

    /**
     * @param int $track_question_template_id
     * @param array $data
     * @return TrackQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTrackQuestionValueTemplate($track_question_template_id, $data);

    /**
     * @param int $track_question_template_id
     * @param int $track_question_value_template_id
     * @param array $data
     * @return TrackQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTrackQuestionValueTemplate($track_question_template_id, $track_question_value_template_id, $data);

    /**
     * @param int $track_question_template_id
     * @param int $track_question_value_template_id
     * @param array $data
     * @return TrackQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTrackQuestionValueTemplate($track_question_template_id, $track_question_value_template_id);
}