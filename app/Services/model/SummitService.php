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

use App\Events\MyScheduleAdd;
use App\Events\MyScheduleRemove;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Event;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use Models\foundation\summit\EntityEvents\EntityEventTypeFactory;
use Models\foundation\summit\EntityEvents\SummitEntityEventProcessContext;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEntityEventRepository;
use models\summit\ISummitEventRepository;
use models\summit\Presentation;
use models\summit\PresentationCategory;
use models\summit\PresentationCategoryGroup;
use models\summit\PresentationSlide;
use models\summit\PresentationSpeaker;
use models\summit\PresentationVideo;
use models\summit\Summit;
use models\summit\SummitAirport;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitEntityEvent;
use models\summit\SummitEvent;
use models\summit\SummitEventFactory;
use models\summit\SummitEventFeedback;
use models\summit\SummitEventType;
use models\summit\SummitHotel;
use models\summit\SummitLocationImage;
use models\summit\SummitTicketType;
use models\summit\SummitType;
use models\summit\SummitVenue;
use models\summit\SummitVenueRoom;
use ModelSerializers\SerializerRegistry;
use services\apis\IEventbriteAPI;
use libs\utils\ITransactionService;
use libs\utils\JsonUtils;
use utils\PagingInfo;
use Log;
use DB;
use ArrayAccess;
use Exception;
use DateTime;

/**
 * Class SummitService
 * @package services\model
 */
final class SummitService implements ISummitService
{

    /**
     *  minimun number of minutes that an event must last
     */
    const MIN_EVENT_MINUTES = 15;
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var ISummitEventRepository
     */
    private $event_repository;

    /**
     * @var IEventbriteAPI
     */
    private $eventbrite_api;

    /**
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var ISummitEntityEventRepository
     */
    private $entity_events_repository;


    public function __construct
    (
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        ISummitEntityEventRepository $entity_events_repository,
        IEventbriteAPI $eventbrite_api,
        ITransactionService $tx_service
    )
    {
        $this->event_repository         = $event_repository;
        $this->speaker_repository       = $speaker_repository;
        $this->entity_events_repository = $entity_events_repository;
        $this->eventbrite_api           = $eventbrite_api;
        $this->tx_service               = $tx_service;
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
        $res = $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event)) {
                throw new EntityNotFoundException('event not found on summit!');
            }
            return $attendee->add2Schedule($event);
        });
        Event::fire(new MyScheduleAdd($attendee, $event_id));
        return $res;
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
        $res = $this->tx_service->transaction(function() use($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if(is_null($event)) throw new EntityNotFoundException('event not found on summit!');
            return $attendee->removeFromSchedule($event);
        });
        Event::fire(new MyScheduleRemove($attendee, $event_id));
        return $res;
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

            if(!$event->isAllowFeedback())
                throw new ValidationException(sprintf("event id %s dont allow feedback", $event->ID));

            $attendee_id  = intval($feedback['attendee_id']);
            $attendee     = $summit->getAttendeeById($attendee_id);
            if(!$attendee) throw new EntityNotFoundException();

            $newFeedback  = new SummitEventFeedback();
            $newFeedback->setRate(intval($feedback['rate']));
            $newFeedback->setNote(trim($feedback['note']));
            $newFeedback->setOwner($attendee->getMember());
            $event->addFeedBack($newFeedback);

            return $newFeedback;
        });
    }

    /**
     * @param Summit $summit
     * @param null|int $member_id
     * @param null|\DateTime $from_date
     * @param null|int $from_id
     * @param int $limit
     * @return array
     */
    public function getSummitEntityEvents(Summit $summit, $member_id = null, DateTime $from_date = null, $from_id = null, $limit = 25)
    {
        return $this->tx_service->transaction(function() use($summit, $member_id, $from_date, $from_id, $limit) {

            $global_last_id  = $this->entity_events_repository->getLastEntityEventId($summit);
            $from_id         = !is_null($from_id)? intval($from_id) : null;
            $ctx             = new SummitEntityEventProcessContext($member_id);

            do {

                $last_event_id   = 0;
                $last_event_date = 0;
                // if we got a from id and its greater than the last one, then break
                if(!is_null($from_id) && $global_last_id <= $from_id) break;

                $events = $this->entity_events_repository->getEntityEvents
                (
                    $summit,
                    $member_id,
                    $from_id,
                    $from_date,
                    $limit
                );

                foreach ($events as $e)
                {
                    if($ctx->getListSize() === $limit) break;
                    $last_event_id   = $e->getId();
                    $last_event_date = $e->getCreated();
                    $entity_event_type_processor = EntityEventTypeFactory::getInstance()->build($e, $ctx);
                    $entity_event_type_processor->process();
                }
                // reset if we do not get any data so far, to get next batch
                $from_id   = $last_event_id;
                $from_date = null;
                //post process for summit events , we should send only te last one
                $ctx->postProcessList();
                // we do not  have any any to process
                if($last_event_id == 0 || $global_last_id <= $last_event_id) break;
            } while($ctx->getListSize() < $limit);

            return array($last_event_id, $last_event_date, $ctx->getListValues());
        });
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitEvent
     */
    public function addEvent(Summit $summit, array $data)
    {
        return $this->saveOrUpdateEvent($summit, $data);
    }

    /**
     * @param Summit $summit
     * @param int $event_id
     * @param array $data
     * @return SummitEvent
     */
    public function updateEvent(Summit $summit, $event_id, array $data)
    {
        return $this->saveOrUpdateEvent($summit, $data, $event_id);
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @param null|int $event_id
     * @return SummitEvent
     */
    private function saveOrUpdateEvent(Summit $summit, array $data, $event_id = null)
    {
        $event_repository = $this->event_repository;

        return $this->tx_service->transaction(function() use($summit, $data, $event_id, $event_repository) {

            $start_datetime = null;
            $end_datetime   = null;

            if(isset($data['start_date']) && isset($data['end_date']))
            {
                $start_datetime = intval($data['start_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $end_datetime   = intval($data['end_date']);
                $end_datetime   = new \DateTime("@$end_datetime");
                $interval       = $end_datetime->diff($start_datetime);
                if($interval->i < self::MIN_EVENT_MINUTES )
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "event should last at lest %s minutes  - current duration %s",
                            self::MIN_EVENT_MINUTES,
                            $interval->i
                        )
                    );
            }

            // check start/end datetime with summit
            $event_type    = null;
            if(isset($data['type_id'])) {
                $event_type = $summit->getEventType(intval($data['type_id']));
                if (is_null($event_type)) {
                    throw new EntityNotFoundException(sprintf("event type id %s does not exists!", $data['type_id']));
                }
            }

            $location = null;
            if(isset($data['location_id'])) {
                $location = $summit->getLocation(intval($data['location_id']));
                if (is_null($location)) {
                    throw new EntityNotFoundException(sprintf("location id %s does not exists!", $data['location_id']));
                }
            }

            $summit_types = array();
            if(isset($data['summit_types_id'])) {
                foreach ($data['summit_types_id'] as $summit_type_id) {
                    $summit_type = $summit->getSummitType($summit_type_id);
                    if (is_null($summit_type)) {
                        throw new ValidationException(sprintf("summit type id %s does not exists!", $summit_type_id));
                    }
                    array_push($summit_types, $summit_type);
                }
            }

            if(is_null($event_id)){
                $event = SummitEventFactory::build($event_type);
            }
            else
            {
                $event = $event_repository->getById($event_id);
                if(is_null($event))
                    throw new ValidationException(sprintf("event id %s does not exists!", $event_id));
            }

            if(isset($data['title']))
                $event->setTitle($data['title']);

            if(isset($data['description']))
                $event->setDescription($data['description']);

            if(isset($data['allow_feedback']))
                $event->setAllowFeedBack($data['allow_feedback']);

            if(!is_null($event_type))
                $event->setType($event_type);

            $event->setSummit($summit);

            if(!is_null($location))
                $event->setLocation($location);

            if(!is_null($start_datetime) && !is_null($end_datetime)) {
                $event->StartDate = $start_datetime;
                $event->EndDate   = $end_datetime;

                if(!$summit->isEventInsideSummitDuration($event))
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "event start/end (%s - %s) does not match with summit start/end (%s - %s)",
                            $start_datetime->format('Y-m-d H:i:s'),
                            $end_datetime->format('Y-m-d H:i:s'),
                            $summit->getLocalBeginDate()>format('Y-m-d H:i:s'),
                            $summit->getLocalEndDate()>format('Y-m-d H:i:s')
                        )
                    );
            }

            $event_repository->add($event);

            if(count($summit_types) > 0) {
                $event->clearSummitTypes();
                foreach ($summit_types as $summit_type) {
                    $event->addSummitType($summit_type);
                }
            }

            if(isset($data['tags']) && count($data['tags']) > 0) {
                $event->clearTags();
                foreach ($data['tags'] as $tag) {
                    $event->addTag($tag);
                }
            }

            return $event;
        });
    }

    /**
     * @param Summit $summit
     * @param int $event_id
     * @param array $data
     * @return SummitEvent
     */
    public function publishEvent(Summit $summit, $event_id, array $data)
    {
        $event_repository = $this->event_repository;

        return $this->tx_service->transaction(function () use ($summit, $data, $event_id, $event_repository) {

            $event = $event_repository->getById($event_id);

            if(is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if(is_null($event->getType()))
                throw new EntityNotFoundException(sprintf("event type its not assigned to event id %s!", $event_id));

            if(is_null($event->getSummit()))
                throw new EntityNotFoundException(sprintf("summit its not assigned to event id %s!", $event_id));

            if($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $start_datetime = $event->StartDate;
            $end_datetime   = $event->EndDate;

            if(isset($data['start_date']) && isset($data['end_date'])) {
                $start_datetime = intval($data['start_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $end_datetime = intval($data['end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
            }

            if(is_null($start_datetime))
                throw new ValidationException(sprintf("start_date its not assigned to event id %s!", $event_id));

            if(is_null($end_datetime))
                throw new ValidationException(sprintf("end_date its not assigned to event id %s!", $event_id));

            if(isset($data['location_id']))
            {
                $location = $summit->getLocation(intval($data['location_id']));
                if(is_null($location))
                    throw new EntityNotFoundException(sprintf("location id %s does not exists!", $data['location_id']));

                $event->setLocation($location);
            }

            $current_event_location = $event->getLocation();

                // validate blackout times
            $conflict_events = $event_repository->getPublishedOnSameTimeFrame($event);
            if(!is_null($conflict_events)) {
                foreach ($conflict_events as $c_event) {
                    // if the published event is BlackoutTime or if there is a BlackoutTime event in this timeframe
                    if (($event->getType()->BlackoutTimes || $c_event->getType()->BlackoutTimes) && $event->ID != $c_event->ID) {
                        throw new ValidationException(sprintf("You can't publish on this time frame, it conflicts with event id %s",
                            $c_event->ID));
                    }
                    // if trying to publish an event on a slot occupied by another event
                    if ($current_event_location->getIdentifier() == $c_event->getLocation()->getIdentifier() && $event->ID != $c_event->ID) {
                        throw new ValidationException(sprintf("You can't publish on this time frame, it conflicts with event id %s",
                            $c_event->ID));
                    }

                    // check speakers collisions
                    if ($event instanceof Presentation && $c_event instanceof Presentation && $event->ID != $c_event->ID) {
                        foreach ($event->speakers() as $current_speaker) {
                            foreach ($c_event->speakers() as $c_speaker) {
                                if (intval($c_speaker->ID) === intval($current_speaker->ID)) {
                                    throw new ValidationException
                                    (
                                        sprintf
                                        (
                                            'speaker id % belongs already to another event ( %s) on that time frame',
                                            $c_speaker->ID,
                                            $c_event->ID
                                        )
                                    );
                                }
                            }
                        }
                    }

                }
            }

            $event->unPublish();
            $event->publish();
            $event_repository->add($event);
            return $event;
        });
    }
    /**
     * @param Summit $summit
     * @param int $event_id
     * @return mixed
     */
    public function unPublishEvent(Summit $summit, $event_id)
    {
        $event_repository = $this->event_repository;

        return $this->tx_service->transaction(function () use ($summit, $event_id, $event_repository) {

            $event = $event_repository->getById($event_id);

            if(is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $event->unPublish();
            $event_repository->add($event);
            $this->cleanupAttendeesSchedule($event_id);
            return $event;
        });
    }

    /**
     * @param Summit $summit
     * @param int $event_id
     * @return mixed
     */
    public function deleteEvent(Summit $summit, $event_id)
    {
        $event_repository = $this->event_repository;

        return $this->tx_service->transaction(function () use ($summit, $event_id, $event_repository) {

            $event = $event_repository->getById($event_id);

            if(is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $event_repository->delete($event);
            // clean up summit attendees schedule
            $this->cleanupAttendeesSchedule($event_id);
            return true;
        });
    }

    /**
     * @param int $event_id
     */
    private function cleanupAttendeesSchedule($event_id){
        DB::connection('ss')->delete("DELETE SummitAttendee_Schedule FROM SummitAttendee_Schedule WHERE SummitEventID = {$event_id};");
    }
    /**
     * @param Summit $summit
     * @param $external_order_id
     * @return array
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @throws Exception
     */
    public function getExternalOrder(Summit $summit, $external_order_id)
    {
        try{
            $external_order = $this->eventbrite_api->getOrder($external_order_id);
            if (isset($external_order['attendees']))
            {
                $status             = $external_order['status'];
                $summit_external_id = $external_order['event_id'];
                $order_summit       = Summit::where('ExternalEventId', '=', $summit_external_id)->first();
                if(is_null($summit))
                    throw new EntityNotFoundException('summit does not exists!');
                if(intval($summit->ID) !== intval($order_summit->ID))
                    throw new ValidationException('order %s does not belongs to current summit!', $external_order_id);
                if($status !== 'placed')
                    throw new ValidationException(sprintf('invalid order status %s for order %s',$status, $external_order_id));
                $attendees = array();
                foreach($external_order['attendees'] as $a)
                {

                   $ticket_external_id = intval($a['ticket_class_id']);
                   $ticket_type        = SummitTicketType::where('ExternalId', '=', $ticket_external_id)->first();
                   $redeem_attendee    = SummitAttendeeTicket::where('ExternalOrderId', '=' , trim($external_order_id))
                       ->where('ExternalAttendeeId','=',$a['id'])
                       ->first();;

                   if(!is_null($redeem_attendee)) continue;
                   if(is_null($ticket_type))
                       throw new EntityNotFoundException(sprintf('external ticket type %s not found!', $ticket_external_id));

                   array_push($attendees, array(
                       'external_id' => intval($a['id']),
                       'first_name'  => $a['profile']['first_name'],
                       'last_name'   => $a['profile']['last_name'],
                       'company'     => $a['profile']['company'],
                       'email'       => $a['profile']['email'],
                       'job_title'   => $a['profile']['job_title'],
                       'status'      => $a['status'],
                       'ticket_type' => array
                       (
                           'id'          => intval($ticket_type->ID),
                           'name'        => $ticket_type->Name,
                           'external_id' => $ticket_external_id,
                       )
                   ));
                }
                if(count($attendees) === 0)
                    throw new ValidationException(sprintf('order %s already redeem!', $external_order_id));

                return array('id' => intval($external_order_id), 'attendees' => $attendees);
            }
        }
        catch(ClientException $ex1){
            if($ex1->getCode() === 400)
                throw new EntityNotFoundException('external order does not exists!');
            if($ex1->getCode() === 403)
                throw new EntityNotFoundException('external order does not exists!');
            throw $ex1;
        }
        catch(Exception $ex){
            throw $ex;
        }
    }

    /**
     * @param Summit $summit
     * @param int $me_id
     * @param int $external_order_id
     * @param int $external_attendee_id
     * @return SummitAttendee
     */
    public function confirmExternalOrderAttendee(Summit $summit, $me_id, $external_order_id, $external_attendee_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $me_id, $external_order_id, $external_attendee_id){

            try{
                $external_order = $this->eventbrite_api->getOrder($external_order_id);
                if (isset($external_order['attendees']))
                {
                    $external_attendee = null;
                    foreach($external_order['attendees'] as $a)
                    {
                        if(intval($a['id']) === intval($external_attendee_id)) {
                            $external_attendee = $a;
                            break;
                        }
                    }

                    if(is_null($external_attendee))
                        throw new EntityNotFoundException(sprintf('attendee %s not found!', $external_attendee_id));

                    $ticket_external_id = intval($external_attendee['ticket_class_id']);
                    $ticket_type = SummitTicketType::where('ExternalId', '=', $ticket_external_id)->first();
                    if(is_null($ticket_type))
                        throw new EntityNotFoundException(sprintf('ticket type %s not found!', $ticket_external_id));;

                    $status             = $external_order['status'];
                    $summit_external_id = $external_order['event_id'];
                    $order_summit       = Summit::where('ExternalEventId', '=', $summit_external_id)->first();
                    if(is_null($summit))
                        throw new EntityNotFoundException('summit does not exists!');
                    if(intval($summit->ID) !== intval($order_summit->ID))
                        throw new ValidationException('order %s does not belongs to current summit!', $external_order_id);
                    if($status !== 'placed')
                        throw new ValidationException(sprintf('invalid order status %s for order %s',$status, $external_order_id));

                    $old_attendee = SummitAttendee::where('MemberID', '=', $me_id)->where('SummitID','=', $summit->ID)->first();

                    if(!is_null($old_attendee))
                        throw new ValidationException
                        (
                            'attendee already exist for current summit!'
                        );

                    $old_ticket = SummitAttendeeTicket
                        ::where('ExternalOrderId','=', $external_order_id)
                        ->where('ExternalAttendeeId','=', $external_attendee_id)->first();

                    if(!is_null($old_ticket))
                        throw new ValidationException
                        (
                            sprintf
                            (
                                'order %s already redeem for attendee id %s !',
                                $external_order_id,
                                $external_attendee_id
                            )
                        );

                    $attendee           = new SummitAttendee;
                    $attendee->MemberID = $me_id;
                    $attendee->SummitID = $summit->ID;
                    $attendee->save();

                    $ticket                     = new SummitAttendeeTicket;
                    $ticket->ExternalOrderId    = intval($external_order_id);
                    $ticket->ExternalAttendeeId = intval($external_attendee_id);
                    $ticket->TicketBoughtDate   = $external_attendee['created'];
                    $ticket->TicketChangedDate  = $external_attendee['changed'];
                    $ticket->TicketTypeID       = $ticket_type->getIdentifier();
                    $ticket->OwnerID            = $attendee->ID;
                    $ticket->save();

                    return $attendee;
                }
            }
            catch(ClientException $ex1){
                if($ex1->getCode() === 400)
                    throw new EntityNotFoundException('external order does not exists!');
                if($ex1->getCode() === 403)
                    throw new EntityNotFoundException('external order does not exists!');
                throw $ex1;
            }
            catch(Exception $ex){
                throw $ex;
            }

        });
    }

    /**
     * @param Summit $summit
     * @param string $expand
     * @return array
     */
    public function getSummitData(Summit $summit, $expand)
    {
        $data = $summit->toArray();
        // summit types
        $summit_types = array();
        foreach ($summit->summit_types() as $type) {
            array_push($summit_types, $type->toArray());
        }
        $data['summit_types'] = $summit_types;
        // tickets
        $ticket_types = array();
        foreach ($summit->ticket_types() as $ticket) {
            array_push($ticket_types, $ticket->toArray());
        }
        $data['ticket_types'] = $ticket_types;
        //locations
        $locations = array();
        foreach ($summit->locations() as $location) {
            array_push($locations, $location->toArray());
        }
        $data['locations'] = $locations;

        $data['ticket_types'] = $ticket_types;
        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'schedule': {
                        $event_types = array();
                        foreach ($summit->event_types() as $event_type) {
                            array_push($event_types, $event_type->toArray());
                        }
                        $data['event_types'] = $event_types;

                        $sponsors = array();
                        foreach ($summit->sponsors() as $company) {
                            array_push($sponsors, $company->toArray());
                        }
                        $data['sponsors'] = $sponsors;

                        $speakers = array();
                        $res = $this->speaker_repository->getSpeakersBySummit($summit, new PagingInfo(1 , PHP_INT_MAX));
                        foreach ($res->getItems() as $speaker) {
                            array_push($speakers, $speaker->toArray($summit->ID));
                        }
                        $data['speakers'] = $speakers;

                        $presentation_categories = array();
                        foreach ($summit->presentation_categories() as $cat) {
                            array_push($presentation_categories, $cat->toArray());
                        }
                        $data['tracks'] = $presentation_categories;

                        // track_groups
                        $track_groups = array();
                        foreach ($summit->category_groups() as $group) {
                            array_push($track_groups, $group->toArray());
                        }
                        $data['track_groups'] = $track_groups;
                        $schedule = array();
                        list($total, $per_page, $current_page, $last_page, $items) = $summit->schedule(1,
                            PHP_INT_MAX);
                        foreach ($items as $event) {
                            array_push($schedule, $event->toArray());
                        }
                        $data['schedule'] = $schedule;

                    }
                    break;
                }
            }
        }
        $data['timestamp'] = time();
        return $data;
    }
}