<?php namespace services\model;
/**
 * Copyright 2017 OpenStack Foundation
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
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\EmailCreationRequest;
use models\main\File;
use models\summit\Speaker;
use models\summit\PresentationSpeakerSummitAssistanceConfirmationRequest;
use models\summit\SpeakerSummitRegistrationPromoCode;
use models\summit\Summit;
use Illuminate\Http\UploadedFile;
/**
 * Interface ISpeakerService
 * @package services\model
 */
interface ISpeakerService
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return Speaker
     * @throws ValidationException
     */
    public function addSpeakerBySummit(Summit $summit, array $data);

    /**
     * @param array $data
     * @return Speaker
     * @throws ValidationException
     */
    public function addSpeaker(array $data);

    /**
     * @param Summit $summit
     * @param array $data
     * @param Speaker $speaker
     * @return Speaker
     * @throws ValidationException
     */
    public function updateSpeakerBySummit(Summit $summit, Speaker $speaker, array $data);

    /**
     * @param array $data
     * @param Speaker $speaker
     * @return Speaker
     * @throws ValidationException
     */
    public function updateSpeaker(Speaker $speaker, array $data);

    /**
     * @param Speaker $speaker
     * @param Summit $summit
     * @param string $reg_code
     * @return SpeakerSummitRegistrationPromoCode
     * @throws ValidationException
     */
    public function registerSummitPromoCodeByValue(Speaker $speaker, Summit $summit, $reg_code);

    /**
     * @param int $speaker_id
     * @param UploadedFile $file
     * @param int $max_file_size
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return File
     */
    public function addSpeakerPhoto($speaker_id, UploadedFile $file,  $max_file_size = 10485760);

    /**
     * @param Speaker $speaker_from
     * @param Speaker $speaker_to
     * @param array $data
     * @return void
     */
    public function merge(Speaker $speaker_from, Speaker $speaker_to, array $data);

    /**
     * @param int $speaker_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSpeaker($speaker_id);

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function addSpeakerAssistance(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param int $assistance_id
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function updateSpeakerAssistance(Summit $summit, $assistance_id, array $data);

    /**
     * @param Summit $summit
     * @param int $assistance_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSpeakerAssistance(Summit $summit, $assistance_id);


    /**
     * @param Summit $summit
     * @param int $assistance_id
     * @return EmailCreationRequest
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function sendSpeakerSummitAssistanceAnnouncementMail(Summit $summit, $assistance_id);
}