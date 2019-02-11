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
use models\summit\Speaker;
use models\summit\PresentationSpeakerSummitAssistanceConfirmationRequest;
use models\summit\Summit;

/**
 * Class PresentationSpeakerSummitAssistanceConfirmationRequestFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class PresentationSpeakerSummitAssistanceConfirmationRequestFactory
{
    /**
     * @param Summit $summit
     * @param Speaker $speaker
     * @param array $data
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public static function build(Summit $summit, Speaker $speaker, array $data){

        $request = new PresentationSpeakerSummitAssistanceConfirmationRequest();
        $request->setSummit($summit);
        $request->setSpeaker($speaker);
        $request = self::populate($request, $data);
        return $request;
    }

    /**
     * @param PresentationSpeakerSummitAssistanceConfirmationRequest $summit_assistance
     * @param array $data
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public static function populate
    (
        PresentationSpeakerSummitAssistanceConfirmationRequest $summit_assistance,
        array $data
    )
    {
        $on_site_phone = isset($data['on_site_phone']) ? trim($data['on_site_phone'])   : null;
        $registered    = isset($data['registered'])    ? boolval($data['registered'])   : 0;
        $checked_in    = isset($data['checked_in'])    ? boolval($data['checked_in'])   : 0;
        $confirmed     = isset($data['is_confirmed'])  ? boolval($data['is_confirmed']) : 0;

        $summit_assistance->setOnSitePhone($on_site_phone);
        $summit_assistance->setRegistered($registered);
        $summit_assistance->setIsConfirmed($confirmed);
        $summit_assistance->setCheckedIn($checked_in);

        return $summit_assistance;
    }
}