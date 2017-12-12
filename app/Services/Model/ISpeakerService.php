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
use models\exceptions\ValidationException;
use models\summit\PresentationSpeaker;
use models\summit\SpeakerSummitRegistrationPromoCode;
use models\summit\Summit;
/**
 * Interface ISpeakerService
 * @package services\model
 */
interface ISpeakerService
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return PresentationSpeaker
     * @throws ValidationException
     */
    public function addSpeaker(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param array $data
     * @param PresentationSpeaker $speaker
     * @return PresentationSpeaker
     * @throws ValidationException
     */
    public function updateSpeaker(Summit $summit, PresentationSpeaker $speaker, array $data);

    /**
     * @param PresentationSpeaker $speaker
     * @param Summit $summit
     * @param string $reg_code
     * @return SpeakerSummitRegistrationPromoCode
     * @throws ValidationException
     */
    public function registerSummitPromoCodeByValue(PresentationSpeaker $speaker, Summit $summit, $reg_code);
}