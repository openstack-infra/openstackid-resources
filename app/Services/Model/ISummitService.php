<?php namespace services\model;
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
use Illuminate\Http\UploadedFile;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\File;
use models\main\Member;
use models\summit\ConfirmationExternalOrderRequest;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitEvent;
use models\summit\SummitEventFeedback;
use models\summit\SummitScheduleEmptySpot;
use utils\Filter;
/**
 * Interface ISummitService
 * @package services\model
 */
interface ISummitService
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitEvent
     */
    public function addEvent(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param int $event_id
     * @param array $data
     * @param null|Member $current_member
     * @return SummitEvent
     */
    public function updateEvent(Summit $summit, $event_id, array $data, Member $current_member = null);

    /**
     * @param Summit $summit
     * @param $event_id
     * @param array $data
     * @return mixed
     */
    public function publishEvent(Summit $summit, $event_id, array $data);

    /**
     * @param Summit $summit
     * @param $event_id
     * @return mixed
     */
    public function unPublishEvent(Summit $summit, $event_id);

    /**
     * @param Summit $summit
     * @param $event_id
     * @return mixed
     */
    public function deleteEvent(Summit $summit, $event_id);

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @param bool $check_rsvp
     * @return bool
     */
    public function addEventToMemberSchedule(Summit $summit, Member $member, $event_id, $check_rsvp = true);

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @param bool $check_rsvp
     * @return void
     */
    public function removeEventFromMemberSchedule(Summit $summit, Member $member, $event_id, $check_rsvp = true);

    /**
     * @param Summit $summit
     * @param SummitEvent $event
     * @param array $feedback
     * @return SummitEventFeedback
     */
    public function addEventFeedback(Summit $summit, SummitEvent $event, array $feedback);

    /**
     * @param Summit $summit
     * @param SummitEvent $event
     * @param array $feedback
     * @return SummitEventFeedback
     */
    public function updateEventFeedback(Summit $summit, SummitEvent $event, array $feedback);

    /**
     * @param Summit $summit
     * @param null|int $member_id
     * @param null|\DateTime $from_date
     * @param null|int $from_id
     * @param null|int $limit
     * @return array
     */
    public function getSummitEntityEvents(Summit $summit, $member_id = null, \DateTime $from_date = null, $from_id = null, $limit = 25);

    /**
     * @param Summit $summit
     * @param $external_order_id
     * @return array
     */
    public function getExternalOrder(Summit $summit, $external_order_id);

    /**
     * @param ConfirmationExternalOrderRequest $request
     * @return SummitAttendee
     */
    public function confirmExternalOrderAttendee(ConfirmationExternalOrderRequest $request);

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @throws EntityNotFoundException
     */
    public function removeEventFromMemberFavorites(Summit $summit, Member $member, $event_id);

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function addEventToMemberFavorites(Summit $summit, Member $member, $event_id);

    /**
     * @param Summit $summit
     * @param Member $member
     * @param $event_id
     * @return bool
     */
    public function unRSVPEvent(Summit $summit, Member $member, $event_id);

    /**
     * @param Summit $summit
     * @param int $event_id
     * @param UploadedFile $file
     * @param int $max_file_size
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return File
     */
    public function addEventAttachment(Summit $summit, $event_id, UploadedFile $file,  $max_file_size = 10485760);

    /**
     * @param Summit $summit
     * @param Filter $filter
     * @return SummitScheduleEmptySpot[]
     */
    public function getSummitScheduleEmptySpots
    (
        Summit $summit,
        Filter $filter
    );

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return bool
     */
    public function unPublishEvents(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return bool
     */
    public function updateAndPublishEvents(Summit $summit, array $data);

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return bool
     */
    public function updateEvents(Summit $summit, array $data);

    /**
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return Summit
     */
    public function addSummit(array $data);

    /**
     * @param int $summit_id
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return Summit
     */
    public function updateSummit($summit_id, array $data);

    /**
     * @param int $summit_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSummit($summit_id);

    /**
     * @param int $current_member_id
     * @param int $speaker_id
     * @param int $presentation_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function addSpeaker2Presentation($current_member_id, $speaker_id, $presentation_id);

    /**
     * @param int $current_member_id
     * @param int $speaker_id
     * @param int $presentation_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function removeSpeaker2Presentation($current_member_id, $speaker_id, $presentation_id);
}