<?php namespace App\Http\Controllers;
/**
 * Copyright 2015 OpenStack Foundation
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

use models\oauth2\IResourceServerContext;
use models\summit\PresentationSpeaker;
use models\summit\Summit;

/**
 * Class CheckMeSpeakerStrategy
 * @package App\Http\Controllers
 */
class CheckMeSpeakerStrategy implements ICheckSpeakerStrategy
{

    /**
     * @var IResourceServerContext
     */
    protected $resource_server_context;

    /**
     * CheckMeAttendeeStrategy constructor.
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct(IResourceServerContext $resource_server_context)
    {
        $this->resource_server_context = $resource_server_context;
    }

    /**
     * @param int $speaker_id
     * @param Summit $summit
     * @return null|PresentationSpeaker
     */
    public function check($speaker_id, Summit $summit)
    {
        if (strtolower($speaker_id) === 'me') {
            $member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($member_id)) {
                return null;
            }
            return $summit->getSpeakerByMemberId($member_id, false);
        }
        return $summit->getSpeaker(intval($speaker_id, false));
    }
}