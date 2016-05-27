<?php
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

namespace App\Http\Controllers;

use models\oauth2\IResourceServerContext;
use models\summit\Summit;
use models\summit\SummitAttendee;

/**
 * Class CheckMyOwnAttendeeStrategy
 * @package App\Http\Controllers
 */
final class CheckMyOwnAttendeeStrategy extends CheckMeAttendeeStrategy implements ICheckAttendeeStrategy
{

    /**
     * @param int $attendee_id
     * @param Summit $summit
     * @return null|SummitAttendee
     * @throws \HTTP401UnauthorizedException
     */
    public function check($attendee_id, Summit $summit)
    {
        $attendee = parent::check($attendee_id, $summit);
        if(!$attendee) return null;
        $attendee_member_id = intval($attendee->getMember()->getId());
        $member_id          = $this->resource_server_context->getCurrentUserExternalId();
        if(is_null($member_id) || ($attendee_member_id !== $member_id)) throw new \HTTP401UnauthorizedException;
        return $attendee;
    }
}