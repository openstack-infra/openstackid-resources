<?php namespace services\model;

use Illuminate\Contracts\Queue\EntityNotFoundException;
use libs\utils\ITransactionService;
use models\main\Member;
use models\summit\Presentation;
use models\summit\PresentationSpeaker;
use models\summit\PresentationSpeakerFeedback;
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
final class SummitService implements ISummitService
{

    /**
     * @var ITransactionService
     */
    private $tx_service;

    public function __construct(ITransactionService $tx_service)
    {
        $this->tx_service = $tx_service;
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @return bool
     * @throws \Exception
     */
    public function addEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event)) {
                throw new \Exception('event not found on summit!');
            }

            return $attendee->add2Schedule($event);
        });
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return bool
     * @throws \Exception
     */
    public function checkInAttendeeOnEvent(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if(is_null($event)) throw new \Exception('event not found on summit!');
            return $attendee->checkIn($event);
        });
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @return bool
     * @throws \Exception
     */
    public function removeEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if(is_null($event)) throw new \Exception('event not found on summit!');
            return $attendee->removeFromSchedule($event);
        });
    }

    /**
     * @param Summit $summit
     * @param PresentationSpeaker $speaker
     * @param Presentation $presentation
     * @param array $feedback
     * @return bool
     */
    public function addSpeakerFeedback(
        Summit $summit,
        PresentationSpeaker $speaker,
        Presentation $presentation,
        array $feedback
    )
    {

        return $this->tx_service->transaction(function() use($speaker,$presentation,$feedback) {

            $newFeedback = new PresentationSpeakerFeedback();
            $newFeedback->Rate    = $feedback['rate'];
            $newFeedback->Note    = $feedback['note'];
            $newFeedback->OwnerID = $feedback['owner_id'];
            $newFeedback->EventID = $presentation->ID;

            $owner = Member::find($newFeedback->OwnerID);
            if(is_null($owner)) throw new EntityNotFoundException();

            $speaker->addFeedBack($newFeedback);


            return true;
        });

    }

    /**
     * @param Summit $summit
     * @param SummitEvent $event
     * @param array $feedback
     * @return bool
     */
    public function addEventFeedback(Summit $summit, SummitEvent $event, array $feedback)
    {

        return $this->tx_service->transaction(function() use($summit,$event,$feedback) {

            $newFeedback          = new SummitEventFeedback();
            $newFeedback->Rate    = $feedback['rate'];
            $newFeedback->Note    = $feedback['note'];
            $newFeedback->OwnerID = $feedback['owner_id'];
            $newFeedback->EventID = $event->ID;

            $owner = Member::find($newFeedback->OwnerID);
            if(is_null($owner)) throw new EntityNotFoundException();

            $event->addFeedBack($newFeedback);

            return true;
        });

    }
}