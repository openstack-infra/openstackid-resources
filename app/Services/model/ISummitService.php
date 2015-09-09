<?php namespace services\model;

use models\summit\Presentation;
use models\summit\PresentationSpeaker;
use models\summit\PresentationSpeakerFeedback;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitEntityEvent;
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
interface ISummitService
{
    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @return bool
     */
    public function addEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id);

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @return bool
     */
    public function removeEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id);

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return bool
     */
    public function checkInAttendeeOnEvent(Summit $summit, SummitAttendee $attendee, $event_id);


    /**
     * @param Summit $summit
     * @param PresentationSpeaker $speaker
     * @param Presentation $presentation
     * @param array $feedback
     * @return PresentationSpeakerFeedback
     */
    public function addSpeakerFeedback(Summit $summit, PresentationSpeaker $speaker, Presentation $presentation , array $feedback);

    /**
     * @param Summit $summit
     * @param Presentation $presentation
     * @param array $feedback
     * @return PresentationSpeakerFeedback[]
     */
    public function addSpeakerFeedbackAll(Summit $summit, Presentation $presentation , array $feedback);


    /**
     * @param Summit $summit
     * @param SummitEvent $event
     * @param array $feedback
     * @return SummitEventFeedback
     */
    public function addEventFeedback(Summit $summit, SummitEvent $event, array $feedback);


    /**
     * @param Summit $summit
     * @param null|int $member_id
     * @param null|\DateTime $from_date
     * @param null|int $from_id
     * @return SummitEntityEvent[]
     */
    public function getSummitEntityEvents(Summit $summit, $member_id = null, \DateTime $from_date = null, $from_id = null);
}