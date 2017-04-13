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

use App\Events\MyFavoritesAdd;
use App\Events\MyFavoritesRemove;
use App\Events\MyScheduleAdd;
use App\Events\MyScheduleRemove;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Event;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\main\ITagRepository;
use Models\foundation\summit\EntityEvents\EntityEventTypeFactory;
use Models\foundation\summit\EntityEvents\SummitEntityEventProcessContext;
use models\main\Member;
use models\main\Tag;
use models\summit\ConfirmationExternalOrderRequest;
use models\summit\IRSVPRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitAttendeeRepository;
use models\summit\ISummitAttendeeTicketRepository;
use models\summit\ISummitEntityEventRepository;
use models\summit\ISummitEventRepository;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitEvent;
use models\summit\SummitEventFactory;
use models\summit\SummitEventFeedback;
use services\apis\IEventbriteAPI;
use libs\utils\ITransactionService;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Log;

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

    /**
     * @var ISummitAttendeeTicketRepository
     */
    private $ticket_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var ISummitAttendeeRepository
     */
    private $attendee_repository;

    /**
     * @var ITagRepository
     */
    private $tag_repository;

    /**
     * @var IRSVPRepository
     */
    private $rsvp_repository;

    /**
     * SummitService constructor.
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param ISummitEntityEventRepository $entity_events_repository
     * @param ISummitAttendeeTicketRepository $ticket_repository
     * @param ISummitAttendeeRepository $attendee_repository
     * @param IMemberRepository $member_repository
     * @param ITagRepository $tag_repository
     * @param IRSVPRepository $rsvp_repository,
     * @param IEventbriteAPI $eventbrite_api
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitEventRepository          $event_repository,
        ISpeakerRepository              $speaker_repository,
        ISummitEntityEventRepository    $entity_events_repository,
        ISummitAttendeeTicketRepository $ticket_repository,
        ISummitAttendeeRepository       $attendee_repository,
        IMemberRepository               $member_repository,
        ITagRepository                  $tag_repository,
        IRSVPRepository                 $rsvp_repository,
        IEventbriteAPI                  $eventbrite_api,
        ITransactionService             $tx_service
    )
    {
        $this->event_repository         = $event_repository;
        $this->speaker_repository       = $speaker_repository;
        $this->entity_events_repository = $entity_events_repository;
        $this->ticket_repository        = $ticket_repository;
        $this->member_repository        = $member_repository;
        $this->attendee_repository      = $attendee_repository;
        $this->tag_repository           = $tag_repository;
        $this->rsvp_repository          = $rsvp_repository;
        $this->eventbrite_api           = $eventbrite_api;
        $this->tx_service               = $tx_service;
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @param bool $check_rsvp
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addEventToAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id, $check_rsvp = true)
    {
        try {
            $this->tx_service->transaction(function () use ($summit, $attendee, $event_id, $check_rsvp) {
                $event = $summit->getScheduleEvent($event_id);
                if (is_null($event)) {
                    throw new EntityNotFoundException('event not found on summit!');
                }
                if(!Summit::allowToSee($event, $attendee->getMember()))
                    throw new EntityNotFoundException('event not found on summit!');

                if($check_rsvp && $event->hasRSVP() && !$event->getIssExternalRSVP())
                    throw new ValidationException("event has rsvp set on it!");

                $attendee->add2Schedule($event);
            });
            Event::fire(new MyScheduleAdd($attendee, $event_id));
        }
        catch (UniqueConstraintViolationException $ex){
            throw new ValidationException
            (
                sprintf('Event %s already belongs to attendee %s schedule.', $event_id, $attendee->getId())
            );
        }
    }

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function addEventToMemberFavorites(Summit $summit, Member $member, $event_id){
        try {
            $this->tx_service->transaction(function () use ($summit, $member, $event_id) {
                $event = $summit->getScheduleEvent($event_id);
                if (is_null($event)) {
                    throw new EntityNotFoundException('event not found on summit!');
                }
                if(!Summit::allowToSee($event, $member))
                    throw new EntityNotFoundException('event not found on summit!');
                $member->addFavoriteSummitEvent($event);
            });
            Event::fire(new MyFavoritesAdd($member, $summit, $event_id));
        }
        catch (UniqueConstraintViolationException $ex){
            throw new ValidationException
            (
                sprintf('Event %s already belongs to member %s favorites.', $event_id, $member->getId())
            );
        }
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function checkInAttendeeOnEvent(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        $this->tx_service->transaction(function () use ($summit, $attendee, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event))
                throw new EntityNotFoundException('event not found on summit!');
            if(!Summit::allowToSee($event, $attendee->getMember()))
                throw new EntityNotFoundException('event not found on summit!');
            $attendee->checkIn($event);
        });
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param int $event_id
     * @param boolean $check_rsvp
     * @return void
     * @throws \Exception
     */
    public function removeEventFromAttendeeSchedule(Summit $summit, SummitAttendee $attendee, $event_id, $check_rsvp = true)
    {
        $this->tx_service->transaction(function () use ($summit, $attendee, $event_id, $check_rsvp) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event))
                throw new EntityNotFoundException('event not found on summit!');

            if($check_rsvp && $event->hasRSVP() && !$event->getIssExternalRSVP())
                throw new ValidationException("event has rsvp set on it!");

            $attendee->removeFromSchedule($event);
        });

        Event::fire(new MyScheduleRemove($attendee, $event_id));
    }


    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @throws EntityNotFoundException
     */
    public function removeEventFromMemberFavorites(Summit $summit, Member $member, $event_id){
        $this->tx_service->transaction(function () use ($summit, $member, $event_id) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event))
                throw new EntityNotFoundException('event not found on summit!');
            $member->removeFavoriteSummitEvent($event);
        });

        Event::fire(new MyFavoritesRemove($member, $summit, $event_id));
    }

    /**
     * @param Summit $summit
     * @param SummitEvent $event
     * @param array $data
     * @return SummitEventFeedback
     */
    public function addEventFeedback(Summit $summit, SummitEvent $event, array $data)
    {

        return $this->tx_service->transaction(function () use ($summit, $event, $data) {

            if (!$event->isAllowFeedback())
                throw new ValidationException(sprintf("event id %s does not allow feedback", $event->getIdentifier()));

            $member      = null;

            // check for attendee
            $attendee_id = isset($data['attendee_id']) ? intval($data['attendee_id']) : null;
            $member_id   = isset($data['member_id'])   ? intval($data['member_id']) : null;
            if(!is_null($attendee_id)) {
                $attendee = $summit->getAttendeeById($attendee_id);
                if (!$attendee) throw new EntityNotFoundException();
                $member = $attendee->getMember();
            }

            // check by member
            if(!is_null($member_id)) {
                $member = $this->member_repository->getById($member_id);
            }

            if (is_null($member))
                throw new EntityNotFoundException('member not found!.');

            if(!Summit::allowToSee($event, $member))
                throw new EntityNotFoundException('event not found on summit!.');

            // check older feedback
            $older_feedback = $member->getFeedbackByEvent($event);

            if(count($older_feedback) > 0 )
                throw new ValidationException(sprintf("you already sent feedback for event id %s!.", $event->getIdentifier()));

            $newFeedback = new SummitEventFeedback();
            $newFeedback->setRate(intval($data['rate']));
            $note        = isset($data['note']) ? trim($data['note']) : "";
            $newFeedback->setNote($note);
            $newFeedback->setOwner($member);
            $event->addFeedBack($newFeedback);

            return $newFeedback;
        });
    }

    /**
     * @param Summit $summit
     * @param SummitEvent $event
     * @param array $data
     * @return SummitEventFeedback
     * @internal param array $feedback
     */
    public function updateEventFeedback(Summit $summit, SummitEvent $event, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $event, $data) {

            if (!$event->isAllowFeedback())
                throw new ValidationException(sprintf("event id %s does not allow feedback", $event->getIdentifier()));

            $member      = null;

            // check for attendee
            $attendee_id = isset($data['attendee_id']) ? intval($data['attendee_id']) : null;
            $member_id   = isset($data['member_id'])   ? intval($data['member_id']) : null;
            if(!is_null($attendee_id)) {
                $attendee = $summit->getAttendeeById($attendee_id);
                if (!$attendee) throw new EntityNotFoundException();
                $member = $attendee->getMember();
            }

            // check by member
            if(!is_null($member_id)) {
                $member = $this->member_repository->getById($member_id);
            }

            if (is_null($member))
                throw new EntityNotFoundException('member not found!.');

            if(!Summit::allowToSee($event, $member))
                throw new EntityNotFoundException('event not found on summit!.');

            // check older feedback
            $feedback = $member->getFeedbackByEvent($event);

            if(count($feedback) == 0 )
                throw new ValidationException(sprintf("you dont have feedback for event id %s!.", $event->getIdentifier()));
            $feedback = $feedback[0];
            $feedback->setRate(intval($data['rate']));
            $note    = isset($data['note']) ? trim($data['note']) : "";
            $feedback->setNote($note);

            return $feedback;
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
        return $this->tx_service->transaction(function () use ($summit, $member_id, $from_date, $from_id, $limit) {

            $global_last_id = $this->entity_events_repository->getLastEntityEventId($summit);
            $from_id        = !is_null($from_id) ? intval($from_id) : null;
            $member         = !is_null($member_id) && $member_id > 0 ? $this->member_repository->getById($member_id): null;
            $ctx            = new SummitEntityEventProcessContext($member);

            do {

                $last_event_id   = 0;
                $last_event_date = 0;
                // if we got a from id and its greater than the last one, then break
                if (!is_null($from_id) && $global_last_id <= $from_id) break;

                $events = $this->entity_events_repository->getEntityEvents
                (
                    $summit,
                    $member_id,
                    $from_id,
                    $from_date,
                    $limit
                );

                foreach ($events as $e) {

                    if ($ctx->getListSize() === $limit) break;

                    $last_event_id               = $e->getId();
                    $last_event_date             = $e->getCreated();
                    try {
                        $entity_event_type_processor = EntityEventTypeFactory::getInstance()->build($e, $ctx);
                        $entity_event_type_processor->process();
                    }
                    catch(\InvalidArgumentException $ex1){
                        Log::info($ex1);
                    }
                    catch(\Exception $ex){
                        Log::error($ex);
                    }
                }
                // reset if we do not get any data so far, to get next batch
                $from_id = $last_event_id;
                $from_date = null;
                //post process for summit events , we should send only te last one
                $ctx->postProcessList();
                // we do not  have any any to process
                if ($last_event_id == 0 || $global_last_id <= $last_event_id) break;
            } while ($ctx->getListSize() < $limit);

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

        return $this->tx_service->transaction(function () use ($summit, $data, $event_id, $event_repository) {

            $start_datetime = null;
            $end_datetime   = null;

            if (isset($data['start_date']) && isset($data['end_date'])) {
                $start_datetime   = intval($data['start_date']);
                $start_datetime   = new \DateTime("@$start_datetime");

                $end_datetime     = intval($data['end_date']);
                $end_datetime     = new \DateTime("@$end_datetime");

                $interval_seconds = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
                $minutes          = $interval_seconds / 60;
                if ($minutes < self::MIN_EVENT_MINUTES)
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "event should last at lest %s minutes  - current duration %s",
                            self::MIN_EVENT_MINUTES,
                            $minutes
                        )
                    );
            }

            $event_type = null;
            if (isset($data['type_id'])) {
                $event_type = $summit->getEventType(intval($data['type_id']));
                if (is_null($event_type)) {
                    throw new EntityNotFoundException(sprintf("event type id %s does not exists!", $data['type_id']));
                }
            }

            $track = null;

            if(isset($data['track_id'])){
                $track = $summit->getPresentationCategory(intval($data['track_id']));
                if(is_null($track)){
                    throw new EntityNotFoundException(sprintf("track id %s does not exists!", $data['track_id']));
                }
            }

            $location = null;
            if (isset($data['location_id'])) {
                $location = $summit->getLocation(intval($data['location_id']));
                if (is_null($location)) {
                    throw new EntityNotFoundException(sprintf("location id %s does not exists!", $data['location_id']));
                }
            }

            if (is_null($event_id)) {
                $event = SummitEventFactory::build($event_type);
            } else {
                $event = $event_repository->getById($event_id);
                if (is_null($event))
                    throw new ValidationException(sprintf("event id %s does not exists!", $event_id));
                $event_type = $event->getType();
            }

            if (isset($data['title']))
                $event->setTitle(trim($data['title']));

            if (isset($data['description']))
                $event->setAbstract(trim($data['description']));

            if (isset($data['social_summary']))
                $event->setSocialSummary(trim($data['social_summary']));

            if (isset($data['allow_feedback']))
                $event->setAllowFeedBack($data['allow_feedback']);

            if (!is_null($event_type))
                $event->setType($event_type);

            if(is_null($event_id) && is_null($event_type)){
                // is event is new one and we dont provide an event type ...
                throw new ValidationException('type_id is mandatory!');
            }

            if(!is_null($track))
            {
                $event->setCategory($track);
            }

            // is event is new and we dont provide speakers ...
            if(is_null($event_id) && !is_null($event_type) && $event_type->isPresentationType() && !isset($data['speakers']))
                throw new ValidationException('speakers data is required for presentations!');

            $event->setSummit($summit);

            if (!is_null($location))
                $event->setLocation($location);

            // check start/end datetime with summit
            if (!is_null($start_datetime) && !is_null($end_datetime)) {
                // set local time from UTC
                $event->setStartDate($start_datetime);
                $event->setEndDate($end_datetime);

                if (!$summit->isEventInsideSummitDuration($event))
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "event start/end (%s - %s) does not match with summit start/end (%s - %s)",
                            $start_datetime->format('Y-m-d H:i:s'),
                            $end_datetime->format('Y-m-d H:i:s'),
                            $summit->getLocalBeginDate()->format('Y-m-d H:i:s'),
                            $summit->getLocalEndDate()->format('Y-m-d H:i:s')
                        )
                    );
            }

            if (isset($data['tags']) && count($data['tags']) > 0) {
                $event->clearTags();
                foreach ($data['tags'] as $str_tag) {
                    $tag = $this->tag_repository->getByTag($str_tag);
                    if($tag == null) $tag = new Tag($str_tag);
                    $event->addTag($tag);
                }
            }

            if (isset($data['speakers'])  && !is_null($event_type) && $event_type->isPresentationType()) {
                $event->clearSpeakers();
                foreach ($data['speakers'] as $speaker_id) {
                    $speaker = $this->speaker_repository->getById(intval($speaker_id));
                    if(is_null($speaker)) throw new EntityNotFoundException(sprintf('speaker id %s', $speaker_id));
                    $event->addSpeaker($speaker);
                }
            }

            if(isset($data['moderator_speaker_id']) && !is_null($event_type) && $event_type->allowsModerator()){
                $speaker_id = intval($data['moderator_speaker_id']);
                if($speaker_id === 0) $event->unsetModerator();
                else
                {
                    $moderator = $this->speaker_repository->getById($speaker_id);
                    if (is_null($moderator)) throw new EntityNotFoundException(sprintf('speaker id %s', $speaker_id));
                    $event->setModerator($speaker);
                }
            }

            $event_repository->add($event);

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

            if (is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if (is_null($event->getType()))
                throw new EntityNotFoundException(sprintf("event type its not assigned to event id %s!", $event_id));

            if (is_null($event->getSummit()))
                throw new EntityNotFoundException(sprintf("summit its not assigned to event id %s!", $event_id));

            if ($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $start_datetime = $event->getStartDate();
            $end_datetime   = $event->getEndDate();

            if (isset($data['start_date']) && isset($data['end_date'])) {
                $start_datetime = intval($data['start_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $end_datetime = intval($data['end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
            }

            if (is_null($start_datetime))
                throw new ValidationException(sprintf("start_date its not assigned to event id %s!", $event_id));

            if (is_null($end_datetime))
                throw new ValidationException(sprintf("end_date its not assigned to event id %s!", $event_id));

            if (isset($data['location_id'])) {
                $location = $summit->getLocation(intval($data['location_id']));
                if (is_null($location))
                    throw new EntityNotFoundException(sprintf("location id %s does not exists!", $data['location_id']));

                $event->setLocation($location);
            }

            $current_event_location = $event->getLocation();

            // validate blackout times
            $conflict_events = $event_repository->getPublishedOnSameTimeFrame($event);
            if (!is_null($conflict_events)) {
                foreach ($conflict_events as $c_event) {
                    // if the published event is BlackoutTime or if there is a BlackoutTime event in this timeframe
                    if (($event->getType()->isBlackoutTimes() || $c_event->getType()->isBlackoutTimes()) && $event->getId() != $c_event->getId()) {
                        throw new ValidationException
                        (
                            sprintf
                            (
                                "You can't publish on this time frame, it conflicts with event id %s",
                                $c_event->getId()
                            )
                        );
                    }
                    // if trying to publish an event on a slot occupied by another event
                    if (!is_null($current_event_location) &&  !is_null($c_event->getLocation()) && $current_event_location->getId() == $c_event->getLocation()->getId() && $event->getId() != $c_event->getId()) {
                        throw new ValidationException
                        (
                            sprintf
                            (
                                "You can't publish on this time frame, it conflicts with event id %s",
                                $c_event->getId()
                            )
                        );
                    }

                    // check speakers collisions
                    if ($event->getClassName() == 'Presentation' && $c_event->getClassName() == 'Presentation' && $event->getId() != $c_event->getId()) {
                        foreach ($event->getSpeakers() as $current_speaker) {
                            foreach ($c_event->getSpeakers() as $c_speaker) {
                                if (intval($c_speaker->getId()) === intval($current_speaker->getId())) {
                                    throw new ValidationException
                                    (
                                        sprintf
                                        (
                                            'speaker id % belongs already to another event ( %s) on that time frame',
                                            $c_speaker->getId(),
                                            $c_event->getId()
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

            if (is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if ($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $event->unPublish();
            $event_repository->add($event);
            $event_repository->cleanupAttendeesScheduleForEvent($event_id);
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

            if (is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if ($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $event_repository->delete($event);
            // clean up summit attendees schedule
            $event_repository->cleanupAttendeesScheduleForEvent($event_id);
            return true;
        });
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
        try {
            $external_order = $this->eventbrite_api->getOrder($external_order_id);

            if (isset($external_order['attendees'])) {
                $status = $external_order['status'];
                $summit_external_id = $external_order['event_id'];

                if (intval($summit->getSummitExternalId()) !== intval($summit_external_id))
                    throw new ValidationException('order %s does not belongs to current summit!', $external_order_id);

                if ($status !== 'placed')
                    throw new ValidationException(sprintf('invalid order status %s for order %s', $status, $external_order_id));

                $attendees = array();
                foreach ($external_order['attendees'] as $a) {

                    $ticket_external_id = intval($a['ticket_class_id']);
                    $ticket_type = $summit->getTicketTypeByExternalId($ticket_external_id);
                    $external_attendee_id = $a['id'];

                    if (is_null($ticket_type))
                        throw new EntityNotFoundException(sprintf('external ticket type %s not found!', $ticket_external_id));

                    $old_ticket = $this->ticket_repository->getByExternalOrderIdAndExternalAttendeeId
                    (
                        trim($external_order_id), $external_attendee_id
                    );

                    if (!is_null($old_ticket)) continue;

                    $attendees[] = [
                        'external_id' => intval($a['id']),
                        'first_name'  => $a['profile']['first_name'],
                        'last_name'   => $a['profile']['last_name'],
                        'email'       => $a['profile']['email'],
                        'company'     => isset($a['profile']['company']) ? $a['profile']['company'] : null,
                        'job_title'   => isset($a['profile']['job_title']) ? $a['profile']['job_title'] : null,
                        'status'      => $a['status'],
                        'ticket_type' => [
                            'id'          => intval($ticket_type->getId()),
                            'name'        => $ticket_type->getName(),
                            'external_id' => $ticket_external_id,
                        ]
                    ];
                }
                if (count($attendees) === 0)
                    throw new ValidationException(sprintf('order %s already redeem!', $external_order_id));

                return array('id' => intval($external_order_id), 'attendees' => $attendees);
            }
        } catch (ClientException $ex1) {
            if ($ex1->getCode() === 400)
                throw new EntityNotFoundException('external order does not exists!');
            if ($ex1->getCode() === 403)
                throw new EntityNotFoundException('external order does not exists!');
            throw $ex1;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @param ConfirmationExternalOrderRequest $request
     * @return SummitAttendee
     */
    public function confirmExternalOrderAttendee(ConfirmationExternalOrderRequest $request)
    {
        return $this->tx_service->transaction(function () use ($request) {

            try {

                $external_order = $this->eventbrite_api->getOrder($request->getExternalOrderId());

                if (isset($external_order['attendees'])) {

                    $summit_external_id = $external_order['event_id'];

                    if (intval($request->getSummit()->getSummitExternalId()) !== intval($summit_external_id))
                        throw new ValidationException('order %s does not belongs to current summit!', $request->getExternalOrderId());

                    $external_attendee = null;
                    foreach ($external_order['attendees'] as $a) {
                        if (intval($a['id']) === intval($request->getExternalAttendeeId())) {
                            $external_attendee = $a;
                            break;
                        }
                    }

                    if (is_null($external_attendee))
                        throw new EntityNotFoundException(sprintf('attendee %s not found!', $request->getExternalAttendeeId()));

                    $ticket_external_id = intval($external_attendee['ticket_class_id']);
                    $ticket_type = $request->getSummit()->getTicketTypeByExternalId($ticket_external_id);

                    if (is_null($ticket_type))
                        throw new EntityNotFoundException(sprintf('ticket type %s not found!', $ticket_external_id));;

                    $status = $external_order['status'];
                    if ($status !== 'placed')
                        throw new ValidationException(sprintf('invalid order status %s for order %s', $status, $request->getExternalOrderId()));

                    $old_attendee = $request->getSummit()->getAttendeeByMemberId($request->getMemberId());

                    if (!is_null($old_attendee))
                        throw new ValidationException
                        (
                            'attendee already exists for current summit!'
                        );

                    $old_ticket = $this->ticket_repository->getByExternalOrderIdAndExternalAttendeeId(
                        $request->getExternalOrderId(),
                        $request->getExternalAttendeeId()
                    );

                    if (!is_null($old_ticket))
                        throw new ValidationException
                        (
                            sprintf
                            (
                                'order %s already redeem for attendee id %s !',
                                $request->getExternalOrderId(),
                                $request->getExternalAttendeeId()
                            )
                        );

                    $ticket = new SummitAttendeeTicket;
                    $ticket->setExternalOrderId( $request->getExternalOrderId());
                    $ticket->setExternalAttendeeId($request->getExternalAttendeeId());
                    $ticket->setBoughtDate(new DateTime($external_attendee['created']));
                    $ticket->setChangedDate(new DateTime($external_attendee['changed']));
                    $ticket->setTicketType($ticket_type);

                    $attendee = new SummitAttendee;
                    $attendee->setMember($this->member_repository->getById($request->getMemberId()));
                    $attendee->setSummit($request->getSummit());
                    $attendee->addTicket($ticket);

                    $this->attendee_repository->add($attendee);

                    return $attendee;
                }
            } catch (ClientException $ex1) {
                if ($ex1->getCode() === 400)
                    throw new EntityNotFoundException('external order does not exists!');
                if ($ex1->getCode() === 403)
                    throw new EntityNotFoundException('external order does not exists!');
                throw $ex1;
            } catch (Exception $ex) {
                throw $ex;
            }

        });
    }

    /**
     * @param Summit $summit
     * @param SummitAttendee $attendee
     * @param $event_id
     * @return bool
     */
    public function unRSVPEvent(Summit $summit, SummitAttendee $attendee, $event_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $attendee, $event_id) {

            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event)) {
                throw new EntityNotFoundException('event not found on summit!');
            }

            if(!Summit::allowToSee($event, $attendee->getMember()))
                throw new EntityNotFoundException('event not found on summit!');

            $rsvp = $attendee->getRsvpByEvent($event_id);

            if(is_null($rsvp))
                throw new ValidationException(sprintf("rsvp for event id %s does not exist for your attendee", $event_id));

            $this->rsvp_repository->delete($rsvp);

            $this->removeEventFromAttendeeSchedule($summit, $attendee, $event_id ,false);

            return true;
        });
    }
}