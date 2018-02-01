<?php namespace models\summit;
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
use models\utils\IBaseRepository;
/**
 * Interface ISpeakerSummitRegistrationPromoCodeRepository
 * @package models\summit
 */
interface ISpeakerSummitRegistrationPromoCodeRepository
    extends IBaseRepository
{
    /**
     * @param PresentationSpeaker $speaker
     * @param Summit $summit
     * @return SpeakerSummitRegistrationPromoCode
     */
    public function getBySpeakerAndSummit(PresentationSpeaker $speaker, Summit $summit);

    /**
     * @param string $code
     * @param Summit $summit
     * @return bool
     */
    public function isAssignedCode($code, Summit $summit);

    /**
     * @param string $code
     * @param Summit $summit
     * @return SpeakerSummitRegistrationPromoCode
     */
    public function getNotAssignedCode($code, Summit $summit);

    /**
     * @param string $code
     * @param Summit $summit
     * @return SpeakerSummitRegistrationPromoCode
     */
    public function getAssignedCode($code, Summit $summit);

    /**
     * @param Summit $summit
     * @param string $type
     * @return SpeakerSummitRegistrationPromoCode
     */
    public function getNextAvailableByType(Summit $summit, $type);
}