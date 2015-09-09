<?php namespace services\model;

use models\exceptions\EntityNotFoundException;
use libs\utils\ITransactionService;
use libs\utils\JsonUtils;
use models\exceptions\ValidationException;
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
     * @param SummitEvent $event
     * @param array $feedback
     * @return SummitEventFeedback
     */
    public function addEventFeedback(Summit $summit, SummitEvent $event, array $feedback)
    {

        return $this->tx_service->transaction(function() use($summit, $event, $feedback) {

            if(!$event->AllowFeedBack)
                throw new ValidationException(sprintf("event id %s dont allow feedback", $event->ID));

            $attendee_id  = intval($feedback['attendee_id']);
            $attendee     = SummitAttendee::find($attendee_id);
            if(is_null($attendee)) throw new EntityNotFoundException();

            $newFeedback          = new SummitEventFeedback();
            $newFeedback->Rate    = $feedback['rate'];
            $newFeedback->Note    = $feedback['note'];
            $newFeedback->OwnerID = $attendee->MemberID;
            $newFeedback->EventID = $event->ID;

            $event->addFeedBack($newFeedback);

            return $newFeedback;
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
                    $entity = $summit->getScheduleEvent($e->EntityID);

                    if($e->Type === 'UPDATE')
                    {
                        $metadata          = !empty($metadata) ? json_decode($metadata, true): array();
                        $published_old     = isset($metadata['pub_old']) ? (bool)intval($metadata['pub_old']) : false;
                        $published_current = isset($metadata['pub_new']) ? (bool)intval($metadata['pub_new']) : false;

                        // the event was not published at the moment of UPDATE .. then skip it!
                        if(!isset($metadata['pub_old']) && isset($metadata['pub_new']) && !$published_current) continue;

                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                        );

                        if(!is_null($entity)) // if event exists its bc its published
                        {
                            $row['entity'] = $entity->toArray();

                            if($entity instanceof Presentation){
                                unset($row['entity']['speakers']);
                                $speakers = array();
                                foreach($entity->speakers() as $speaker)
                                {
                                    array_push($speakers, $speaker->toArray());
                                }
                                $row['entity']['speakers'] = $speakers;
                            }
                            $row['type'] = $published_current && !$published_old && !is_null($entity) ?  'INSERT' : 'UPDATE';
                            array_push($list, $row);
                        }
                        else // if doesnt exists on schedule delete it
                        {
                            $row['type']   = 'DELETE';
                            array_push($list, $row);
                        }
                    }
                    else if($e->Type === 'DELETE')
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
                        $entity = $summit->getScheduleEvent($e->EntityID);
                        $row    = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'type'       => $e->Type,
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                        );

                        if($e->Type === 'INSERT' && !is_null($entity))
                        {
                            $row['entity'] = $entity->toArray();
                            array_push($list, $row);
                        }
                        else if($e->Type === 'DELETE')
                        {
                            array_push($list, $row);
                        }
                    }
                }
                break;
                case 'SummitType':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitType::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitEventType':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitEventType::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'PresentationSpeaker':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationSpeaker::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitTicketType':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitTicketType::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitVenueRoom':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitVenueRoom::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitVenue':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitVenue::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitLocationMap':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitLocationMap::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitHotel':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitHotel::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SummitAirport':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitAirport::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'PresentationCategory':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationCategory::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'PresentationSlide':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationSlide::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'PresentationVideo':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationVideo::find(inval($e->EntityID));
                        if(is_null($entity)) continue;
                        $row = array
                        (
                            'id'         => $e->ID,
                            'created'    => JsonUtils::toEpoch($e->Created),
                            'class_name' => $e->EntityClassName,
                            'entity_id'  => $e->EntityID,
                            'type'       => $e->Type,
                            'entity'     => $entity->toArray()
                        );
                        array_push($list, $row);
                    }
                    else if($e->Type === 'DELETE')
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
                case 'SpeakerFromEvent':
                {
                    $metadata        = !empty($metadata) ? json_decode($metadata, true): array();
                    if(count($metadata) === 0) continue;
                    $presentation_id = isset($metadata['presentation_id']) ? intval($metadata['presentation_id']) : null;
                    if(is_null($presentation_id)) continue;
                    $entity          = $summit->getScheduleEvent($presentation_id);

                    $row = array
                    (
                        'id'         => $e->ID,
                        'created'    => JsonUtils::toEpoch($e->Created),
                        'class_name' => 'Presentation',
                        'entity_id'  => $presentation_id,
                    );
                    $row['type']   = 'UPDATE';
                    $row['entity'] = $entity->toArray();

                    unset($row['entity']['speakers']);
                    $speakers = array();
                    foreach($entity->speakers() as $speaker)
                    {
                        array_push($speakers, $speaker->toArray());
                    }
                    $row['entity']['speakers'] = $speakers;

                    array_push($list, $row);
                }
            }
        }
        return $list;
    }

}