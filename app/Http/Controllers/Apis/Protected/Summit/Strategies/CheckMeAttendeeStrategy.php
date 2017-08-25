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
 * Class CheckMeAttendeeStrategy
 * @package App\Http\Controllers
 */
class CheckMeAttendeeStrategy implements ICheckAttendeeStrategy
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
     * @param $attendee_id
     * @param Summit $summit
     * @return null|SummitAttendee
     */
    public function check($attendee_id, Summit $summit)
    {
        if (strtolower($attendee_id) === 'me') {
            $member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($member_id)) {
                return null;
            }
            $attendee = $summit->getAttendeeByMemberId($member_id);
        } else {
            $attendee = $summit->getAttendeeById(intval($attendee_id));
        }

        return $attendee;
    }
}