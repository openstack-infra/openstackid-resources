<?php namespace services\model;

use models\exceptions\EntityNotFoundException;
use libs\utils\ITransactionService;
use libs\utils\JsonUtils;
use models\exceptions\ValidationException;
use models\summit\PresentationCategory;
use models\summit\PresentationSlide;
use models\summit\PresentationSpeaker;
use models\summit\PresentationVideo;
use models\summit\Summit;
use models\summit\SummitAirport;
use models\summit\SummitAttendee;
use models\summit\SummitEntityEvent;
use models\summit\SummitEvent;
use models\summit\SummitEventFeedback;
use models\summit\SummitEventType;
use models\summit\SummitHotel;
use models\summit\SummitLocationMap;
use models\summit\SummitTicketType;
use models\summit\SummitType;
use models\summit\SummitVenue;
use models\summit\SummitVenueRoom;

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
     * @throws EntityNotFoundException
     */
    public function addEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event)) {
                throw new EntityNotFoundException('event not found on summit!');
            }

            return $attendee->add2Schedule($event);
        });
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return bool
     * @throws EntityNotFoundException
     */
    public function checkInAttendeeOnEvent(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if(is_null($event)) throw new EntityNotFoundException('event not found on summit!');
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
    public function removeEventFromAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if(is_null($event)) throw new EntityNotFoundException('event not found on summit!');
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

                        if(!is_null($entity)) // if event exists its bc its published
                        {
                            $type = $published_current && !$published_old && !is_null($entity) ?  'INSERT' : 'UPDATE';
                            array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $type, $entity));
                        }
                        else // if does not exists on schedule delete it
                        {
                            array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, 'DELETE'));
                        }
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'MySchedule':
                {
                    if($member_id === $e->OwnerID)
                    {
                        if($e->Type === 'INSERT' && !is_null($entity))
                        {
                            $entity = $summit->getScheduleEvent($e->EntityID);
                            if(is_null($entity)) continue;
                            array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                        }
                        else if($e->Type === 'DELETE')
                        {
                            array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                        }
                    }
                }
                break;
                case 'SummitType':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitType::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitEventType':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitEventType::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'PresentationSpeaker':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationSpeaker::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitTicketType':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitTicketType::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitVenueRoom':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitVenueRoom::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitVenue':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitVenue::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitLocationMap':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitLocationMap::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitHotel':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitHotel::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SummitAirport':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = SummitAirport::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'PresentationCategory':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationCategory::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'PresentationSlide':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationSlide::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'PresentationVideo':
                {
                    if($e->Type === 'UPDATE' || $e->Type === "INSERT")
                    {
                        $entity = PresentationVideo::find(intval($e->EntityID));
                        if(is_null($entity)) continue;
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type, $entity));
                    }
                    else if($e->Type === 'DELETE')
                    {
                        array_push($list, $this->serializeSummitEntityEvent($e, $e->EntityClassName, $e->Type));
                    }
                }
                break;
                case 'SpeakerFromPresentation':
                {
                    $metadata        = !empty($metadata) ? json_decode($metadata, true): array();
                    if(count($metadata) === 0) continue;
                    $presentation_id = isset($metadata['presentation_id']) ? intval($metadata['presentation_id']) : null;
                    if(is_null($presentation_id)) continue;
                    $entity          = $summit->getScheduleEvent($presentation_id);
                    array_push($list, $this->serializeSummitEntityEvent($e, 'Presentation', 'UPDATE', $entity));
                }
                break;
                case 'SponsorFromEvent':
                {
                    $metadata        = !empty($metadata) ? json_decode($metadata, true): array();
                    if(count($metadata) === 0) continue;
                    $event_id = isset($metadata['event_id']) ? intval($metadata['event_id']) : null;
                    if(is_null($event_id)) continue;
                    $entity          = $summit->getScheduleEvent($event_id);
                    array_push($list, $this->serializeSummitEntityEvent($e, 'SummitEvent', 'UPDATE', $entity));
                }
                break;
            }
        }
        return $list;
    }

    private function serializeSummitEntityEvent(SummitEntityEvent $e, $class_name, $type, $entity = null)
    {
        $row = array
        (
            'id'         => intval($e->ID),
            'created'    => JsonUtils::toEpoch($e->Created),
            'class_name' => $class_name,
            'entity_id'  => intval($e->EntityID),
            'type'       => $type,

        );

        if(!is_null($entity))
        {
            $data = $entity->toArray();
            if(isset($data['speakers']))
            {
                unset($data['speakers']);
                $speakers = array();
                foreach($entity->speakers() as $speaker)
                {
                    array_push($speakers, $speaker->toArray());
                }
                $data['speakers'] = $speakers;
            }

            if(isset($data['sponsors']))
            {
                unset($data['sponsors']);
                $sponsors = array();
                foreach($entity->sponsors() as $sponsor)
                {
                    array_push($sponsors, $sponsor->toArray());
                }
                $data['sponsors'] = $sponsors;
            }
            $row['entity'] = $data;
        }

        return $row;
    }
}