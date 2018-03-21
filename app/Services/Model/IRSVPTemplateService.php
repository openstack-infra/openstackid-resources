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
use App\Models\Foundation\Summit\Events\RSVP\RSVPQuestionTemplate;
use App\Models\Foundation\Summit\Events\RSVP\RSVPQuestionValueTemplate;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
/**
 * Interface IRSVPTemplateService
 * @package App\Services\Model
 */
interface IRSVPTemplateService
{
    /**
     * @param Summit $summit
     * @param int $template_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTemplate(Summit $summit, $template_id);

    /**
     * @param Summit $summit
     * @param $template_id
     * @param array $payload
     * @return RSVPQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addQuestion(Summit $summit, $template_id, array $payload);

    /**
     * @param Summit $summit
     * @param int $template_id
     * @param int $question_id
     * @param array $payload
     * @return RSVPQuestionTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateQuestion(Summit $summit, $template_id, $question_id, array $payload);

    /**
     * @param Summit $summit
     * @param int $template_id
     * @param int $question_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteQuestion(Summit $summit, $template_id, $question_id);

    /**
     * @param Summit $summit
     * @param int $template_id
     * @param int $question_id
     * @param array $payload
     * @return RSVPQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addQuestionValue($summit, $template_id, $question_id, $payload);

    /**
     * @param Summit $summit
     * @param int $template_id
     * @param int $question_id
     * @param int $value_id
     * @param array $payload
     * @return RSVPQuestionValueTemplate
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateQuestionValue($summit, $template_id, $question_id, $value_id, $payload);

    /**
     * @param Summit $summit
     * @param int $template_id
     * @param int $question_id
     * @param int $value_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteQuestionValue($summit, $template_id, $question_id, $value_id);

}