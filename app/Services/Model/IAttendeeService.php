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
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
/**
 * Interface IAttendeeService
 * @package App\Services\Model
 */
interface IAttendeeService
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitAttendee
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function addAttendee(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param int $attendee_id
     * @return void
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function deleteAttendee(Summit $summit, $attendee_id);

    /**
     * @param Summit $summit
     * @param int $attendee_id
     * @param array $data
     * @return SummitAttendee
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updateAttendee(Summit $summit, $attendee_id, array $data);

    /**
     * @param SummitAttendee $attendee
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return SummitAttendeeTicket
     */
    public function addAttendeeTicket(SummitAttendee $attendee, array $data);
}