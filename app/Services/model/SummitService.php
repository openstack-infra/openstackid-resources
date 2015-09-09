<?php namespace services\model;

use Illuminate\Contracts\Queue\EntityNotFoundException;
use libs\utils\ITransactionService;
use libs\utils\JsonUtils;
use models\main\Member;
use models\summit\Presentation;
use models\summit\PresentationSpeaker;
use models\summit\PresentationSpeakerFeedback;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitEntityEvent;
use models\summit\SummitEvent;
use models\summit\SummitEventFeedback;
use Psy\Util\Json;

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


    /**
     * @param Summit $summit
     * @param null|int $member_id
     * @param null|\DateTime $from_date
     * @param null|int $from_id
     * @return SummitEntityEvent[]
     */
    public function getSummitEntityEvents(Summit $summit, $member_id = null, \DateTime $from_date = null, $from_id = null)
    {
        $events = $summit->getEntityEvents($from_id, $from_date);
        $list   = array();

        foreach($events as $e)
        {
            $metadata = $e->Metadata;
            switch($e->EntityClassName)
            {
                case 'Presentation':
                case 'SummitEvent':
                {
                    if($e->Type === 'INSERT' && !empty($metadata))
                    {
                        $metadata  = json_decode($metadata, true);
                        $published = isset($metadata['pub_new']) ? (bool)intval($metadata['pub_new']) : false;

                        if($published) array_push($list, array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'type'       => 'INSERT',
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'entity'     => $summit->getScheduleEvent($e->EntityID)->toArray()
                        ));
                    }
                    else if($e->Type === 'UPDATE' && !empty($metadata))
                    {
                        $metadata  = json_decode($metadata, true);
                        $published = isset($metadata['pub_new']) ? (bool)intval($metadata['pub_new']) : false;

                        if($published) array_push($list, array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'type'       => 'UPDATE',
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'entity'     => $summit->getScheduleEvent($e->EntityID)->toArray()
                        ));
                    }
                    else if($e->Type === 'DELETE' && !empty($metadata))
                    {

                        array_push($list, array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'type'       => 'DELETE',
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                        ));
                    }
                }
                break;
                case 'MySchedule':
                {
                    if($member_id === $e->OwnerID)
                    {
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'type'       => $e->Type,
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                        );

                        if($e->Type === 'INSERT')
                            $row['entity'] = $summit->getScheduleEvent($e->EntityID)->toArray();

                        array_push($list, $row);
                    }
                }
                break;
            }
        }
        return $list;
    }
}