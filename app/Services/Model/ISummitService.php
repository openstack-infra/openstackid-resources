<?php namespace services\model;

use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\Member;
use models\summit\ConfirmationExternalOrderRequest;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitEvent;
use models\summit\SummitEventFeedback;

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
     * @return SummitEvent
     */
    public function updateEvent(Summit $summit, $event_id, array $data);

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
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @param bool $check_rsvp
     * @return bool
     */
    public function addEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id, $check_rsvp = true);

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @param bool $check_rsvp
     * @return void
     */
    public function removeEventFromAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id, $check_rsvp = true);

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return void
     */
    public function checkInAttendeeOnEvent(Summit $summit, SummitAttendee $attendee, $event_id);

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
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return bool
     */
    public function unRSVPEvent(Summit $summit, SummitAttendee $attendee, $event_id);
}