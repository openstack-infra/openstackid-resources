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
use App\Events\SummitDeleted;
use App\Events\SummitUpdated;
use App\Http\Utils\FileUploader;
use App\Http\Utils\SwiftBucket;
use App\Models\Foundation\Summit\Factories\SummitFactory;
use App\Models\Foundation\Summit\Repositories\IDefaultSummitEventTypeRepository;
use App\Models\Utils\IntervalParser;
use App\Permissions\IPermissionsManager;
use App\Services\Model\AbstractService;
use App\Services\Model\IFolderService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\File;
use models\main\ICompanyRepository;
use models\main\IGroupRepository;
use models\main\IMemberRepository;
use models\main\ITagRepository;
use Models\foundation\summit\EntityEvents\EntityEventTypeFactory;
use Models\foundation\summit\EntityEvents\SummitEntityEventProcessContext;
use models\main\Member;
use models\main\Tag;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\ConfirmationExternalOrderRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\IRSVPRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitAttendeeRepository;
use models\summit\ISummitAttendeeTicketRepository;
use models\summit\ISummitEntityEventRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use models\summit\Presentation;
use models\summit\PresentationType;
use models\summit\Speaker;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitEvent;
use models\summit\SummitEventFactory;
use models\summit\SummitEventFeedback;
use models\summit\SummitEventType;
use models\summit\SummitEventWithFile;
use models\summit\SummitGroupEvent;
use models\summit\SummitScheduleEmptySpot;
use services\apis\IEventbriteAPI;
use libs\utils\ITransactionService;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Log;
use utils\Filter;
use utils\FilterElement;
use utils\FilterParser;
use utils\Order;
use utils\OrderElement;
use utils\PagingInfo;
use DateInterval;
/**
 * Class SummitService
 * @package services\model
 */
final class SummitService extends AbstractService implements ISummitService
{

    /**
     *  minimun number of minutes that an event must last
     */
    const MIN_EVENT_MINUTES = 5;

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
     * @var IAbstractCalendarSyncWorkRequestRepository
     */
    private $calendar_sync_work_request_repository;

    /**
     * @var IFolderService
     */
    private $folder_service;

    /**
     * @var ICompanyRepository
     */
    private $company_repository;

    /**
     * @var IGroupRepository
     */
    private $group_repository;

    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var IDefaultSummitEventTypeRepository
     */
    private $default_event_types_repository;

    /**
     * @var IPermissionsManager
     */
    private $permissions_manager;

    /**
     * SummitService constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param ISummitEntityEventRepository $entity_events_repository
     * @param ISummitAttendeeTicketRepository $ticket_repository
     * @param ISummitAttendeeRepository $attendee_repository
     * @param IMemberRepository $member_repository
     * @param ITagRepository $tag_repository
     * @param IRSVPRepository $rsvp_repository
     * @param IAbstractCalendarSyncWorkRequestRepository $calendar_sync_work_request_repository
     * @param IEventbriteAPI $eventbrite_api
     * @param IFolderService $folder_service
     * @param ICompanyRepository $company_repository
     * @param IGroupRepository $group_repository,
     * @param IDefaultSummitEventTypeRepository $default_event_types_repository
     * @param IPermissionsManager $permissions_manager
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitRepository               $summit_repository,
        ISummitEventRepository          $event_repository,
        ISpeakerRepository              $speaker_repository,
        ISummitEntityEventRepository    $entity_events_repository,
        ISummitAttendeeTicketRepository $ticket_repository,
        ISummitAttendeeRepository       $attendee_repository,
        IMemberRepository               $member_repository,
        ITagRepository                  $tag_repository,
        IRSVPRepository                 $rsvp_repository,
        IAbstractCalendarSyncWorkRequestRepository $calendar_sync_work_request_repository,
        IEventbriteAPI                    $eventbrite_api,
        IFolderService                    $folder_service,
        ICompanyRepository                $company_repository,
        IGroupRepository                  $group_repository,
        IDefaultSummitEventTypeRepository $default_event_types_repository,
        IPermissionsManager               $permissions_manager,
        ITransactionService               $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->summit_repository                     = $summit_repository;
        $this->event_repository                      = $event_repository;
        $this->speaker_repository                    = $speaker_repository;
        $this->entity_events_repository              = $entity_events_repository;
        $this->ticket_repository                     = $ticket_repository;
        $this->member_repository                     = $member_repository;
        $this->attendee_repository                   = $attendee_repository;
        $this->tag_repository                        = $tag_repository;
        $this->rsvp_repository                       = $rsvp_repository;
        $this->calendar_sync_work_request_repository = $calendar_sync_work_request_repository;
        $this->eventbrite_api                        = $eventbrite_api;
        $this->folder_service                        = $folder_service;
        $this->company_repository                    = $company_repository;
        $this->group_repository                      = $group_repository;
        $this->default_event_types_repository        = $default_event_types_repository;
        $this->permissions_manager                   = $permissions_manager;
    }

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @param bool $check_rsvp
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addEventToMemberSchedule(Summit $summit, Member $member, $event_id, $check_rsvp = true)
    {
        try {
            $this->tx_service->transaction(function () use ($summit, $member, $event_id, $check_rsvp) {

                $event = $summit->getScheduleEvent($event_id);

                if (is_null($event)) {
                    throw new EntityNotFoundException('event not found on summit!');
                }

                if(!Summit::allowToSee($event, $member))
                    throw new EntityNotFoundException('event not found on summit!');

                if($check_rsvp && $event->hasRSVP() && !$event->isExternalRSVP())
                    throw new ValidationException("event has rsvp set on it!");

                $member->add2Schedule($event);

                if($member->hasSyncInfoFor($summit)) {
                    Log::info(sprintf("synching externally event id %s", $event_id));
                    $sync_info = $member->getSyncInfoBy($summit);
                    $request   = new MemberEventScheduleSummitActionSyncWorkRequest();
                    $request->setType(AbstractCalendarSyncWorkRequest::TypeAdd);
                    $request->setSummitEvent($event);
                    $request->setOwner($member);
                    $request->setCalendarSyncInfo($sync_info);
                    $this->calendar_sync_work_request_repository->add($request);
                }

            });
            Event::fire(new MyScheduleAdd($member ,$summit, $event_id));
        }
        catch (UniqueConstraintViolationException $ex){
            throw new ValidationException
            (
                sprintf('Event %s already belongs to member %s schedule.', $event_id, $member->getId())
            );
        }
    }

    /**
     * @param Summit $summit
     * @param Member $member
     * @param int $event_id
     * @param boolean $check_rsvp
     * @return void
     * @throws \Exception
     */
    public function removeEventFromMemberSchedule(Summit $summit, Member $member, $event_id, $check_rsvp = true)
    {
        $this->tx_service->transaction(function () use ($summit, $member, $event_id, $check_rsvp) {
            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event))
                throw new EntityNotFoundException('event not found on summit!');

            if($check_rsvp && $event->hasRSVP() && !$event->isExternalRSVP())
                throw new ValidationException("event has rsvp set on it!");

            $member->removeFromSchedule($event);

            if($member->hasSyncInfoFor($summit)) {
                Log::info(sprintf("unsynching externally event id %s", $event_id));
                $sync_info = $member->getSyncInfoBy($summit);
                $request   = new MemberEventScheduleSummitActionSyncWorkRequest();
                $request->setType(AbstractCalendarSyncWorkRequest::TypeRemove);
                $request->setSummitEvent($event);
                $request->setOwner($member);
                $request->setCalendarSyncInfo($sync_info);
                $this->calendar_sync_work_request_repository->add($request);
            }
        });

        Event::fire(new MyScheduleRemove($member,$summit, $event_id));
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
     * @param null|Member $current_member
     * @return SummitEvent
     */
    public function updateEvent(Summit $summit, $event_id, array $data, Member $current_member = null)
    {
        return $this->saveOrUpdateEvent($summit, $data, $event_id, $current_member);
    }

    /**
     * @param array $data
     * @param Summit $summit
     * @param SummitEvent $event
     * @return SummitEvent
     * @throws ValidationException
     */
    private function updateEventDates(array $data, Summit $summit, SummitEvent $event){

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $event->setSummit($summit);
            $start_datetime   = intval($data['start_date']);
            $start_datetime   = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime     = intval($data['end_date']);
            $end_datetime     = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());
            $interval_seconds = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
            $minutes          = $interval_seconds / 60;
            if ($minutes < self::MIN_EVENT_MINUTES)
                throw new ValidationException
                (
                    sprintf
                    (
                        "event should last at least %s minutes  - current duration %s",
                        self::MIN_EVENT_MINUTES,
                        $minutes
                    )
                );

            // set local time from UTC
            $event->setStartDate($start_datetime);
            $event->setEndDate($end_datetime);

            if (!$summit->isEventInsideSummitDuration($event))
                throw new ValidationException
                (
                    sprintf
                    (
                        "event start/end (%s - %s) does not match with summit start/end (%s - %s)",
                        $event->getLocalStartDate()->format('Y-m-d H:i:s'),
                        $event->getLocalEndDate()->format('Y-m-d H:i:s'),
                        $summit->getLocalBeginDate()->format('Y-m-d H:i:s'),
                        $summit->getLocalEndDate()->format('Y-m-d H:i:s')
                    )
                );
        }

        return $event;

    }

    /**
     * @param SummitEventType $old_event_type
     * @param SummitEventType $event_type
     * @return bool
     */
    private function canPerformEventTypeTransition(SummitEventType $old_event_type, SummitEventType $event_type){

        if($old_event_type->getId() == $event_type->getId()) return true;
        // cant upgrade from raw event to presentation and vice versa
        if($old_event_type->getClassName() != $event_type->getClassName()) {
            return false;
        }

        $old_is_private = $old_event_type->isPrivate();
        $new_is_private = $event_type->isPrivate();

        if((!$old_is_private && $new_is_private) || ($old_is_private && !$new_is_private))
            return false;

        $old_allow_attach = $old_event_type->isAllowsAttachment();
        $new_allow_attach = $event_type->isAllowsAttachment();

         if((!$old_allow_attach && $new_allow_attach) || ($old_allow_attach && !$new_allow_attach))
            return false;

        return true;
    }


    /**
     * @param Summit $summit
     * @param array $data
     * @param null|int $event_id
     * @param Member|null $current_member
     * @return SummitEvent
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    private function saveOrUpdateEvent(Summit $summit, array $data, $event_id = null, Member $current_member = null)
    {

        return $this->tx_service->transaction(function () use ($summit, $data, $event_id, $current_member) {

            if(!is_null($current_member) && !$this->permissions_manager->canEditFields($current_member, 'SummitEvent', $data)){
                throw new ValidationException(sprintf("user %s cant set requested summit event fields", $current_member->getId()));
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
                if (is_null($location) && intval($data['location_id']) > 0) {
                    throw new EntityNotFoundException(sprintf("location id %s does not exists!", $data['location_id']));
                }
            }

            $event = null;
            // existing event

            if (!is_null($event_id) && intval($event_id) > 0 ) {
                $event = $this->event_repository->getById($event_id);
                if (is_null($event))
                    throw new ValidationException(sprintf("event id %s does not exists!", $event_id));
                $old_event_type = $event->getType();

                // check event type transition ...

                if(!is_null($event_type) && !$this->canPerformEventTypeTransition($old_event_type, $event_type)){
                        throw new ValidationException
                        (
                            sprintf
                            (
                                "invalid event type transition for event id %s ( from %s to %s)",
                                $event_id,
                                $old_event_type->getType(),
                                $event_type->getType()
                            )
                        );
                }
                if(is_null($event_type)) $event_type = $old_event_type;
            }

            // new event
            if (is_null($event))
                $event = SummitEventFactory::build($event_type, $summit);

            // main data

            if (isset($data['title']))
                $event->setTitle(html_entity_decode(trim($data['title'])));

            if (isset($data['description']))
                $event->setAbstract(html_entity_decode(trim($data['description'])));

            if(isset($data['rsvp_link']) && isset($data['rsvp_template_id'])){
                throw new ValidationException("rsvp_link and rsvp_template_id are both set, you need to especify only one");
            }

            if (isset($data['rsvp_link'])) {
                $event->setRSVPLink(html_entity_decode(trim($data['rsvp_link'])));
            }

            if (isset($data['rsvp_template_id'])) {

                $rsvp_template = $summit->getRSVPTemplateById(intval($data['rsvp_template_id']));

                if(is_null($rsvp_template))
                    throw new EntityNotFoundException(sprintf('rsvp template id %s does not belongs to summit id %s', $data['rsvp_template_id'], $summit->getId()));

                if(!$rsvp_template->isEnabled())
                    throw new ValidationException(sprintf('rsvp template id %s is not enabled', $data['rsvp_template_id']));

                $event->setRSVPTemplate($rsvp_template);

                $event->setRSVPMaxUserNumber(intval($data['rsvp_max_user_number']));
                $event->setRSVPMaxUserWaitListNumber(intval($data['rsvp_max_user_wait_list_number']));
            }

            if (isset($data['head_count']))
                $event->setHeadCount(intval($data['head_count']));

            if (isset($data['social_description']))
                $event->setSocialSummary(strip_tags(trim($data['social_description'])));

            if (isset($data['occupancy']))
                $event->setOccupancy($data['occupancy']);

            $event->setAllowFeedBack(isset($data['allow_feedback'])?
                filter_var($data['allow_feedback'], FILTER_VALIDATE_BOOLEAN) :
                false);

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

            $event->setSummit($summit);
            if(!is_null($location))
                $event->setLocation($location);

            if(is_null($location) && isset($data['location_id'])){
                // clear location
                $event->clearLocation();
            }

            $this->updateEventDates($data, $summit, $event);

            if (isset($data['tags'])) {
                $event->clearTags();
                foreach ($data['tags'] as $str_tag) {
                    $tag = $this->tag_repository->getByTag($str_tag);
                    if($tag == null) $tag = new Tag($str_tag);
                    $event->addTag($tag);
                }
            }

            // sponsors

            $sponsors = ($event_type->isUseSponsors() && isset($data['sponsors'])) ?
                $data['sponsors'] : [];

            if($event_type->isAreSponsorsMandatory() && count($sponsors) == 0){
                throw new ValidationException('sponsors are mandatory!');
            }

            if (isset($data['sponsors'])) {
                $event->clearSponsors();
                foreach ($sponsors as $sponsor_id) {
                    $sponsor = $this->company_repository->getById(intval($sponsor_id));
                    if(is_null($sponsor)) throw new EntityNotFoundException(sprintf('sponsor id %s', $sponsor_id));
                    $event->addSponsor($sponsor);
                }
            }

            $this->saveOrUpdatePresentationData($event, $event_type, $data);
            $this->saveOrUpdateSummitGroupEventData($event, $event_type, $data);

            if($event->isPublished())
            {
                $this->validateBlackOutTimesAndTimes($event);
                $event->unPublish();
                $event->publish();
            }

            $this->event_repository->add($event);

            return $event;
        });
    }

    private function saveOrUpdateSummitGroupEventData(SummitEvent $event, SummitEventType $event_type, array $data ){
        if(!$event instanceof SummitGroupEvent) return;

        if(!isset($data['groups']) || count($data['groups']) == 0)
            throw new ValidationException('groups is required');
        $event->clearGroups();

        foreach ($data['groups'] as $group_id) {
            $group = $this->group_repository->getById(intval($group_id));
            if(is_null($group)) throw new EntityNotFoundException(sprintf('group id %s', $group_id));
            $event->addGroup($group);
        }
    }

    /**
     * @param SummitEvent $event
     * @param SummitEventType $event_type
     * @param array $data
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    private function saveOrUpdatePresentationData(SummitEvent $event, SummitEventType $event_type, array $data ){
        if(!$event instanceof Presentation) return;

        // main data
        if(isset($data['attendees_expected_learnt']))
            $event->setAttendeesExpectedLearnt(html_entity_decode($data['attendees_expected_learnt']));

        if(isset($data['level']))
            $event->setLevel($data['level']);

        $event->setAttendingMedia(isset($data['attending_media'])?
            filter_var($data['attending_media'], FILTER_VALIDATE_BOOLEAN) : 0);

        // if we are creating the presentation from admin, then
        // we should mark it as received and complete
        $event->setStatus(Presentation::STATUS_RECEIVED);
        $event->setProgress(Presentation::PHASE_COMPLETE);

        $event->setToRecord(isset($data['to_record'])?
            filter_var($data['to_record'], FILTER_VALIDATE_BOOLEAN): 0);

        // speakers by role
        foreach (Speaker::$AvailableRoles as $availableRole){
            if($event_type instanceof PresentationType && $event_type->shouldUseRole($availableRole)) {

                $speakers = isset($data['speakers']) ? $data['speakers'] : [];
                $speakers = array_filter($speakers, function ($person) use ($availableRole) {
                    return ($person['role'] == $availableRole);
                });

                $speakerCount = count($speakers);

                if ($event_type->isRoleMandatory($availableRole) && $speakerCount == 0) {
                    throw new ValidationException(sprintf('%s are mandatory!', $availableRole));
                }

                if($speakerCount < $event_type->getMinByRole($availableRole)){
                    throw new ValidationException(sprintf('%s min qty is %s!', $availableRole, $event_type->getMinByRole($availableRole)));
                }

                if($speakerCount > $event_type->getMaxByRole($availableRole)){
                    throw new ValidationException(sprintf('%s max qty is %s!', $availableRole, $event_type->getMaxByRole($availableRole)));
                }

                if ($speakerCount > 0 && $event instanceof Presentation) {

                    $event->clearSpeakersByRole($availableRole);

                    foreach ($speakers as $speaker_dto) {
                        $speaker = $this->speaker_repository->getById(intval($speaker_dto['id']));
                        if (is_null($speaker)) throw new EntityNotFoundException(sprintf('speaker id %s', $speaker_dto['id']));
                        $event->addSpeakerByRole($speaker, $availableRole);
                    }
                }
            }
        }

    }

    /**
     * @param Summit $summit
     * @param int $event_id
     * @param array $data
     * @return SummitEvent
     */
    public function publishEvent(Summit $summit, $event_id, array $data)
    {

        return $this->tx_service->transaction(function () use ($summit, $data, $event_id) {

            $event = $this->event_repository->getById($event_id);

            if (is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if (is_null($event->getType()))
                throw new EntityNotFoundException(sprintf("event type its not assigned to event id %s!", $event_id));

            if (is_null($event->getSummit()))
                throw new EntityNotFoundException(sprintf("summit its not assigned to event id %s!", $event_id));

            if ($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $this->updateEventDates($data, $summit, $event);

            $start_datetime = $event->getStartDate();
            $end_datetime   = $event->getEndDate();

            if (is_null($start_datetime))
                throw new ValidationException(sprintf("start_date its not assigned to event id %s!", $event_id));

            if (is_null($end_datetime))
                throw new ValidationException(sprintf("end_date its not assigned to event id %s!", $event_id));

            if (isset($data['location_id'])) {
                $location_id = intval($data['location_id']);
                $event->clearLocation();
                if($location_id > 0){
                    $location    = $summit->getLocation($location_id);
                    if (is_null($location))
                        throw new EntityNotFoundException(sprintf("location id %s does not exists!", $data['location_id']));
                    $event->setLocation($location);
                }
            }

            $this->validateBlackOutTimesAndTimes($event);
            $event->unPublish();
            $event->publish();
            $this->event_repository->add($event);
            return $event;
        });
    }

    private function validateBlackOutTimesAndTimes(SummitEvent $event){
        $current_event_location = $event->getLocation();

        // validate blackout times
        $conflict_events = $this->event_repository->getPublishedOnSameTimeFrame($event);
        if (!is_null($conflict_events)) {
            foreach ($conflict_events as $c_event) {
                // if the published event is BlackoutTime or if there is a BlackoutTime event in this timeframe
                if ((!is_null($current_event_location) && !$current_event_location->isOverrideBlackouts()) &&  ($event->getType()->isBlackoutTimes() || $c_event->getType()->isBlackoutTimes()) && $event->getId() != $c_event->getId()) {
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
                if ($event instanceof Presentation && $c_event instanceof Presentation && $event->getId() != $c_event->getId()) {
                    foreach ($event->getSpeakers() as $current_presentation_speaker) {
                        foreach ($c_event->getSpeakers() as $c_presentation_speaker) {
                            if (intval($c_presentation_speaker->getSpeaker()->getId()) === intval($current_presentation_speaker->getSpeaker()->getId())) {
                                throw new ValidationException
                                (
                                    sprintf
                                    (
                                        "You can't publish Event %s (%s) on this timeframe, speaker %s its presention in room %s at this time.",
                                        $event->getTitle(),
                                        $event->getId(),
                                        $current_presentation_speaker->getSpeaker()->getFullName(),
                                        $c_event->getLocationName()
                                    )
                                );
                            }
                        }
                    }
                }

            }
        }
    }
    /**
     * @param Summit $summit
     * @param int $event_id
     * @return mixed
     */
    public function unPublishEvent(Summit $summit, $event_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $event_id) {

            $event = $this->event_repository->getById($event_id);

            if (is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if ($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $event->unPublish();

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

        return $this->tx_service->transaction(function () use ($summit, $event_id) {

            $event = $this->event_repository->getById($event_id);

            if (is_null($event))
                throw new EntityNotFoundException(sprintf("event id %s does not exists!", $event_id));

            if ($event->getSummit()->getIdentifier() !== $summit->getIdentifier())
                throw new ValidationException(sprintf("event %s does not belongs to summit id %s", $event_id, $summit->getIdentifier()));

            $this->event_repository->delete($event);

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
     * @param Member $member
     * @param $event_id
     * @return bool
     */
    public function unRSVPEvent(Summit $summit, Member $member, $event_id)
    {
        return $this->tx_service->transaction(function () use ($summit, $member, $event_id) {

            $event = $summit->getScheduleEvent($event_id);
            if (is_null($event)) {
                throw new EntityNotFoundException('event not found on summit!');
            }

            if(!Summit::allowToSee($event, $member))
                throw new EntityNotFoundException('event not found on summit!');

            $rsvp = $member->getRsvpByEvent($event_id);

            if(is_null($rsvp))
                throw new ValidationException(sprintf("rsvp for event id %s does not exist for your member", $event_id));

            $this->rsvp_repository->delete($rsvp);

            $this->removeEventFromMemberSchedule($summit, $member, $event_id ,false);

            return true;
        });
    }

    /**
     * @param Summit $summit
     * @param int $event_id
     * @param UploadedFile $file
     * @param int $max_file_size
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return File
     */
    public function addEventAttachment(Summit $summit, $event_id, UploadedFile $file,  $max_file_size = 10485760)
    {
        return $this->tx_service->transaction(function () use ($summit, $event_id, $file, $max_file_size) {

            $allowed_extensions = ['png','jpg','jpeg','gif','pdf'];

            $event = $summit->getEvent($event_id);

            if (is_null($event)) {
                throw new EntityNotFoundException('event not found on summit!');
            }

            if(!$event instanceof SummitEventWithFile){
                throw new ValidationException(sprintf("event id %s does not allow attachments!", $event_id));
            }

            if(!in_array($file->extension(), $allowed_extensions)){
                throw new ValidationException("file does not has a valid extension ('png','jpg','jpeg','gif','pdf').");
            }

            if($file->getSize() > $max_file_size)
            {
                throw new ValidationException(sprintf( "file exceeds max_file_size (%s MB).", ($max_file_size/1024)/1024));
            }

            $uploader   = new FileUploader($this->folder_service, new SwiftBucket);
            $attachment = $uploader->build($file, 'summit-event-attachments', true);
            $event->setAttachment($attachment);

            return $attachment;
        });
    }

    /**
     * @param Summit $summit
     * @param Filter $filter
     * @return SummitScheduleEmptySpot[]
     */
    public function getSummitScheduleEmptySpots
    (
        Summit $summit,
        Filter $filter
    )
    {
        return $this->tx_service->transaction(function () use
        (
            $summit,
            $filter
        ){
            $gaps = [];
            $order = new Order([
                OrderElement::buildAscFor("location_id"),
                OrderElement::buildAscFor("start_date"),
            ]);

            // parse locations ids

            if(!$filter->hasFilter('location_id'))
                throw new ValidationException("missing required filter location_id");

            $location_ids = $filter->getFilterCollectionByField('location_id');

            // parse start_date filter
            $start_datetime_filter = $filter->getFilter('start_date');
            if(is_null($start_datetime_filter))
                throw new ValidationException("missing required filter start_date");
            $start_datetime_unix = intval($start_datetime_filter[0]->getValue());
            $start_datetime = new \DateTime("@$start_datetime_unix");
            // parse end_date filter
            $end_datetime_filter = $filter->getFilter('end_date');
            if(is_null($end_datetime_filter))
                throw new ValidationException("missing required filter end_date");
            $end_datetime_unix = intval($end_datetime_filter[0]->getValue());
            $end_datetime      = new \DateTime("@$end_datetime_unix");
            // gap size filter

            $gap_size_filter = $filter->getFilter('gap');
            if(is_null($end_datetime_filter))
                throw new ValidationException("missing required filter gap");

            $gap_size       = $gap_size_filter[0];

            $summit_time_zone = $summit->getTimeZone();
            $start_datetime->setTimezone($summit_time_zone);
            $end_datetime->setTimezone($summit_time_zone);

            $intervals  = IntervalParser::getInterval($start_datetime, $end_datetime);

            foreach($location_ids as $location_id) {

                foreach($intervals as $interval) {

                    $events_filter = new Filter();
                    $events_filter->addFilterCondition(FilterParser::buildFilter('published', '==', '1'));
                    $events_filter->addFilterCondition(FilterParser::buildFilter('summit_id', '==', $summit->getId()));
                    $events_filter->addFilterCondition(FilterParser::buildFilter('location_id', '==', intval($location_id)));

                    $events_filter->addFilterCondition(FilterParser::buildFilter('start_date', '<', $interval[1]->getTimestamp()));
                    $events_filter->addFilterCondition(FilterParser::buildFilter('end_date', '>', $interval[0]->getTimestamp()));

                    $paging_response = $this->event_repository->getAllByPage
                    (
                        new PagingInfo(1, PHP_INT_MAX),
                        $events_filter,
                        $order
                    );

                    $gap_start_date = $interval[0];
                    $gap_end_date   = clone $gap_start_date;
                    // check published items
                    foreach ($paging_response->getItems() as $event) {

                        while
                        (
                            (
                                $gap_end_date->getTimestamp() + (self::MIN_EVENT_MINUTES * 60)
                            )
                            <= $event->getLocalStartDate()->getTimestamp()
                        ) {
                            $max_gap_end_date = clone $gap_end_date;
                            $max_gap_end_date->setTime(23, 59, 59);
                            if ($gap_end_date->getTimestamp() + (self::MIN_EVENT_MINUTES * 60) > $max_gap_end_date->getTimestamp()) break;
                            $gap_end_date->add(new DateInterval('PT' . self::MIN_EVENT_MINUTES . 'M'));
                        }

                        if ($gap_start_date->getTimestamp() == $gap_end_date->getTimestamp()) {
                            // no gap!
                            $gap_start_date = $event->getLocalEndDate();
                            $gap_end_date = clone $gap_start_date;
                            continue;
                        }

                        // check min gap ...
                        if(self::checkGapCriteria($gap_size, $gap_end_date->diff($gap_start_date)))
                            $gaps[] = new SummitScheduleEmptySpot($location_id, $gap_start_date, $gap_end_date);
                        $gap_start_date = $event->getLocalEndDate();
                        $gap_end_date   = clone $gap_start_date;
                    }

                    // check last possible gap ( from last $gap_start_date till $interval[1]

                    if($gap_start_date < $interval[1]){
                        // last possible gap
                        if(self::checkGapCriteria($gap_size, $interval[1]->diff($gap_start_date)))
                            $gaps[] = new SummitScheduleEmptySpot($location_id, $gap_start_date, $interval[1]);
                    }
                }
            }

            return $gaps;

        });
    }


    /**
     * @param FilterElement $gap_size_criteria
     * @param DateInterval $interval
     * @return bool
     */
    private static function checkGapCriteria
    (
        FilterElement $gap_size_criteria,
        DateInterval $interval
    )
    {
        $total_minutes  = $interval->days * 24 * 60;
        $total_minutes += $interval->h * 60;
        $total_minutes += $interval->i;

        switch($gap_size_criteria->getOperator()){
            case '=':
            {
                return intval($gap_size_criteria->getValue()) == $total_minutes;
            }
            break;
            case '<':
            {
                return $total_minutes < intval($gap_size_criteria->getValue());
            }
            break;
            case '>':
            {
                return $total_minutes > intval($gap_size_criteria->getValue());
            }
            break;
            case '<=':
            {
                return $total_minutes <= intval($gap_size_criteria->getValue());
            }
            break;
            case '>=':
            {
                return $total_minutes >= intval($gap_size_criteria->getValue());
            }
            break;
        }
        return false;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return bool
     */
    public function unPublishEvents(Summit $summit, array $data)
    {
        return $this->tx_service->transaction(function () use
        (
            $summit,
            $data
        )
        {
            foreach ($data['events'] as $event_id){
                $this->unPublishEvent($summit, intval($event_id));
            }

            return true;
        });
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return bool
     */
    public function updateAndPublishEvents(Summit $summit, array $data)
    {
        return $this->tx_service->transaction(function () use
        (
            $summit,
            $data
        )
        {
            foreach ($data['events'] as $event_data){
                $this->updateEvent($summit, intval($event_data['id']), $event_data);
                $this->publishEvent($summit, intval($event_data['id']), $event_data);
            }

            return true;
        });
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return bool
     */
    public function updateEvents(Summit $summit, array $data)
    {
        return $this->tx_service->transaction(function () use
        (
            $summit,
            $data
        )
        {
            foreach ($data['events'] as $event_data){
                $this->updateEvent($summit, intval($event_data['id']), $event_data);
            }

            return true;
        });
    }

    /**
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return Summit
     */
    public function addSummit(array $data)
    {
        return $this->tx_service->transaction(function () use ($data) {

            $name = trim($data['name']);
            $former_summit = $this->summit_repository->getByName($name);
            if(!is_null($former_summit)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.SummitService.AddSummit.NameAlreadyExists',
                        ['name' => $name]
                    )
                );
            }

            $summit = SummitFactory::build($data);
            // seed default event types
            foreach($this->default_event_types_repository->getAll() as $default_event_type){
                $summit->addEventType($default_event_type->buildType($summit));
            }

            $this->summit_repository->add($summit);

            return $summit;

        });
    }

    /**
     * @param int $summit_id
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return Summit
     */
    public function updateSummit($summit_id, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit_id, $data) {

            if(isset($data['name'])) {

                $former_summit = $this->summit_repository->getByName(trim($data['name']));
                if (!is_null($former_summit) && $former_summit->getId() != $summit_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.SummitService.updateSummit.NameAlreadyExists',
                            ['name' => $data['name']]
                        )
                    );
                }
            }

            if(isset($data['active'])) {
                $active = boolval($data['active']);
                $active_summit = $this->summit_repository->getActive();
                if ($active && !is_null($active_summit) && $active_summit->getId() != $summit_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.SummitService.updateSummit.SummitAlreadyActive',
                            ['active_summit_id' => $active_summit->getId()]
                        )
                    );
                }
            }

            $summit = $this->summit_repository->getById($summit_id);

            if(is_null($summit)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.SummitService.updateSummit.SummitNotFound',
                        ['summit_id' => $summit_id]
                    )
                );
            }

            $summit =  SummitFactory::populate($summit, $data);

            Event::fire(new SummitUpdated($summit_id));

            return $summit;
        });
    }

    /**
     * @param int $summit_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSummit($summit_id)
    {
        return $this->tx_service->transaction(function () use ($summit_id) {

            $summit = $this->summit_repository->getById($summit_id);

            if(is_null($summit)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.SummitService.deleteSummit.SummitNotFound',
                        ['summit_id' => $summit_id]
                    )
                );
            }

            $this->summit_repository->delete($summit);

            Event::fire(new SummitDeleted($summit_id));

        });
    }

    /**
     * @param int $current_member_id
     * @param int $speaker_id
     * @param int $presentation_id
     * @param string $role
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return void
     */
    public function addSpeaker2PresentationByRole(int $current_member_id, int $speaker_id, int $presentation_id, string $role)
    {
        return $this->tx_service->transaction(function () use ($current_member_id, $speaker_id, $presentation_id, $role) {
            $current_member = $this->member_repository->getById($current_member_id);
            if(is_null($current_member))
                throw new EntityNotFoundException(sprintf("member %s not found", $current_member_id));

            $current_speaker = $this->speaker_repository->getByMember($current_member);
            if(is_null($current_speaker))
                throw new EntityNotFoundException(sprintf("member %s does not has a speaker profile", $current_member_id));

            $presentation = $this->event_repository->getById($presentation_id);
            if(is_null($presentation))
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if(!$presentation instanceof Presentation)
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if(!$presentation->canEdit($current_speaker))
                throw new ValidationException(sprintf("member %s can not edit presentation %s",
                    $current_member_id,
                    $presentation_id
                    ));

            $presentationType = $presentation->getType();

            if(!$presentationType instanceof PresentationType){
                throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));
            }

            $maxByRole = $presentationType->getMaxByRole($role);
            $countByRole = $presentation->getSpeakerCountByRole($role);
            if($countByRole + 1 > $maxByRole){
                throw new ValidationException(sprintf("%s max qty is %s.", $role, $maxByRole));
            }

            $speaker = $this->speaker_repository->getById(intval($speaker_id));
            if (is_null($speaker))
                throw new EntityNotFoundException(sprintf('speaker %s not found', $speaker_id));

            if($presentation->getProgress() == Presentation::PHASE_TAGS)
                $presentation->setProgress(Presentation::PHASE_SPEAKERS);


            $presentation->addSpeakerByRole($speaker, $role);
        });
    }

    /**
     * @param int $current_member_id
     * @param int $speaker_id
     * @param int $presentation_id
     * @param string $role
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return void
     */
    public function removeSpeakerFromPresentationByRole(int $current_member_id, int $speaker_id, int $presentation_id, string $role)
    {
        return $this->tx_service->transaction(function () use ($current_member_id, $speaker_id, $presentation_id, $role) {

                $current_member = $this->member_repository->getById($current_member_id);
                if(is_null($current_member))
                    throw new EntityNotFoundException(sprintf("member %s not found", $current_member_id));

                $current_speaker = $this->speaker_repository->getByMember($current_member);
                if(is_null($current_speaker))
                    throw new EntityNotFoundException(sprintf("member %s does not has a speaker profile", $current_member_id));

                $presentation = $this->event_repository->getById($presentation_id);
                if(is_null($presentation))
                    throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

                if(!$presentation instanceof Presentation)
                    throw new EntityNotFoundException(sprintf("presentation %s not found", $presentation_id));

            if(!$presentation->canEdit($current_speaker))
                throw new ValidationException(sprintf("member %s can not edit presentation %s",
                    $current_member_id,
                    $presentation_id
                ));

                $speaker = $this->speaker_repository->getById(intval($speaker_id));
                if (is_null($speaker))
                    throw new EntityNotFoundException(sprintf('speaker %s not found', $speaker_id));

                if($presentation->getProgress() == Presentation::PHASE_TAGS)
                    $presentation->setProgress(Presentation::PHASE_SPEAKERS);

                $presentation->removeSpeaker($speaker);
        });
    }

}