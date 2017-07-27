<?php namespace services\model;
/**
 * Copyright 2017 OpenStack Foundation
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
use CalDAVClient\Facade\Exceptions\ForbiddenException;
use CalDAVClient\Facade\Exceptions\NotFoundResourceException;
use CalDAVClient\Facade\Exceptions\ServerErrorException;
use CalDAVClient\Facade\Exceptions\UserUnAuthorizedException;
use Doctrine\DBAL\DBALException;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use services\apis\CalendarSync\CalendarSyncRemoteFacadeFactory;
use utils\PagingInfo;
use libs\utils\ITransactionService;
use Illuminate\Support\Facades\Log;
/**
 * Class MemberActionsCalendarSyncProcessingService
 * @package services\model
 */
final class MemberActionsCalendarSyncProcessingService
implements IMemberActionsCalendarSyncProcessingService
{

    /**
     * @var IAbstractCalendarSyncWorkRequestRepository
     */
    private $work_request_repository;

    /**
     * @var ICalendarSyncInfoRepository
     */
    private $calendar_sync_repository;

    /**
     * @var ITransactionService
     */
    private $tx_manager;

    /**
     * MemberActionsCalendarSyncProcessingService constructor.
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncInfoRepository $calendar_sync_repository
     * @param ITransactionService $tx_manager
     */
    public function __construct
    (
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncInfoRepository $calendar_sync_repository,
        ITransactionService $tx_manager
    )
    {
        $this->work_request_repository  = $work_request_repository;
        $this->calendar_sync_repository = $calendar_sync_repository;
        $this->tx_manager               = $tx_manager;
    }

    /**
     * This method, although expend processing power and db access, is meant to reduce
     * unnecessary calls to external calendars api
     * @param array $requests
     * @return array
     */
    private function preProcessActions(array $requests){

        $work_queue_manager = new MemberScheduleWorkQueueManager();

        foreach ($requests as $request){
            if(! $request instanceof MemberScheduleSummitActionSyncWorkRequest) continue;
            $calendar_sync_info   = $request->getCalendarSyncInfo();
            $register_request     = true;
            if($request instanceof MemberEventScheduleSummitActionSyncWorkRequest){
                $summit_event = $request->getSummitEvent();
                switch ($request->getType()){
                    case AbstractCalendarSyncWorkRequest::TypeRemove:
                    {
                        // check if there is a former add, disregard and omit
                        $pending_requests = $work_queue_manager->getSummitEventRequestFor($calendar_sync_info->getId(), $summit_event->getId());
                        if(count($pending_requests) > 0 ) {
                            foreach ($pending_requests as $pending_request) {
                                if($work_queue_manager->removeRequest($pending_request))
                                    $this->work_request_repository->delete($pending_request);
                            }
                            $this->work_request_repository->delete($request);
                            $register_request = false;
                            continue;
                        }
                    }
                    break;
                    case AbstractCalendarSyncWorkRequest::TypeAdd:
                    {
                        // check if there is a former add, disregard and omit
                        $pending_requests = $work_queue_manager->getSummitEventRequestFor($calendar_sync_info->getId(), $summit_event->getId(), AbstractCalendarSyncWorkRequest::TypeRemove);
                        if(count($pending_requests) > 0 ) {
                            foreach ($pending_requests as $pending_request) {
                                if($work_queue_manager->removeRequest($pending_request))
                                    $this->work_request_repository->delete($pending_request);
                            }
                            $this->work_request_repository->delete($request);
                            $register_request = false;
                            continue;
                        }
                    }
                    break;
                    case AbstractCalendarSyncWorkRequest::TypeUpdate:
                    {
                        // check if there is a former ones, disregard and omit
                        $pending_requests = $work_queue_manager->getSummitEventRequestFor($calendar_sync_info->getId(), $summit_event->getId());
                        if(count($pending_requests) > 0 ) {
                            $this->work_request_repository->delete($request);
                            $register_request = false;
                            continue;
                        }
                    }
                    break;
                }
            }

            if($request instanceof MemberCalendarScheduleSummitActionSyncWorkRequest){
                switch ($request->getType()) {
                    case AbstractCalendarSyncWorkRequest::TypeRemove: {
                        //check if we have pending make calendar on this round ...
                        $pending_calendar_create_request = $work_queue_manager->getCalendarRequestFor($calendar_sync_info->getId(), AbstractCalendarSyncWorkRequest::TypeAdd);
                        $calendar_created = true;
                        if(!is_null($pending_calendar_create_request)){
                            if($work_queue_manager->removeRequest($pending_calendar_create_request))
                                $this->work_request_repository->delete($pending_calendar_create_request);
                            $calendar_created = false;
                        }
                        // delete all pending work ( calendar and events)
                        $pending_requests = $work_queue_manager->getPendingEventsForCalendar($calendar_sync_info->getId());
                        if(count($pending_requests) > 0 ) {
                            foreach($pending_requests as $pending_request) {
                                if($work_queue_manager->removeRequest($pending_request))
                                    $this->work_request_repository->delete($pending_request);
                            }
                        }

                        if(!$calendar_created){
                            // delete the current request ( delete calendar, we never created it )
                            $this->work_request_repository->delete($request);
                            $work_queue_manager->clearPendingEventsForCalendar($calendar_sync_info->getId());
                            // delete revoked credentials;
                            $this->calendar_sync_repository->delete($calendar_sync_info);
                            $register_request = false;
                            continue;
                        }
                    }
                }
            }
            if($register_request)
                $work_queue_manager->registerRequest($request);
        }
        return $work_queue_manager->getPurgedRequests();
    }
    /**
     * @param int $batch_size
     * @return int
     */
    public function processActions($batch_size = 1000)
    {
        return $this->tx_manager->transaction(function() use($batch_size){
            $count = 0;
            $res = $this->work_request_repository->getUnprocessedMemberScheduleWorkRequestActionByPage
            (
                new PagingInfo(1, $batch_size)
            );

            foreach ($this->preProcessActions($res->getItems()) as $request){

                try {
                    if (!$request instanceof MemberScheduleSummitActionSyncWorkRequest) continue;
                    $calendar_sync_info   = $request->getCalendarSyncInfo();
                    $remote_facade        = CalendarSyncRemoteFacadeFactory::getInstance()->build($calendar_sync_info);
                    if (is_null($remote_facade)) continue;
                    $member               = $request->getOwner();
                    $request_type         = $request->getType();
                    $request_sub_type     = $request->getSubType();

                    echo sprintf
                    (
                        "processing work request %s - sub type %s - type %s - member %s - revoked credentials %s",
                                $request->getIdentifier(),
                                $request_sub_type,
                                $request_type,
                                $member->getIdentifier(),
                                $calendar_sync_info->isRevoked()? 1:0
                    ).PHP_EOL;

                    switch ($request_sub_type) {

                        case MemberEventScheduleSummitActionSyncWorkRequest::SubType: {
                            $summit_event = $request->getSummitEvent();
                            switch ($request_type) {
                                case AbstractCalendarSyncWorkRequest::TypeAdd:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $member->add2ScheduleSyncInfo($remote_facade->addEvent($request));
                                    break;
                                case AbstractCalendarSyncWorkRequest::TypeUpdate:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $sync_info    = $member->getScheduleSyncInfoByEvent($summit_event, $calendar_sync_info);
                                    $is_scheduled = $member->isOnSchedule($summit_event);
                                    if(!is_null($sync_info)) {
                                        if(!$is_scheduled)
                                            $member->removeFromScheduleSyncInfo($sync_info);
                                        else
                                            $remote_facade->updateEvent($request, $sync_info);
                                    }
                                    break;
                                case AbstractCalendarSyncWorkRequest::TypeRemove:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $sync_info = $member->getScheduleSyncInfoByEvent($summit_event, $calendar_sync_info);
                                    $remote_facade->deleteEvent($request, $sync_info);
                                    $member->removeFromScheduleSyncInfo($sync_info);
                                    break;
                            }
                        }
                        break;
                        case MemberCalendarScheduleSummitActionSyncWorkRequest::SubType: {
                            switch ($request_type) {
                                case AbstractCalendarSyncWorkRequest::TypeAdd:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $remote_facade->createCalendar($request, $calendar_sync_info);
                                    break;
                                case AbstractCalendarSyncWorkRequest::TypeRemove:
                                    $remote_facade->deleteCalendar($request, $calendar_sync_info);
                                    $member->removeFromCalendarSyncInfo($calendar_sync_info);
                                    break;
                            }
                        }
                        break;
                    }

                    $request->markProcessed();
                    $count++;
                }
                catch(ForbiddenException $ex1){
                    // cant create calendar ...
                    echo 'ForbiddenException !!'.PHP_EOL;
                    Log::warning($ex1);
                }
                catch(UserUnAuthorizedException $ex2){
                    echo 'UserUnAuthorizedException !!'.PHP_EOL;
                    Log::warning($ex2);
                }
                catch(NotFoundResourceException $ex3){
                    echo 'NotFoundResourceException !!'.PHP_EOL;
                    Log::error($ex3);
                }
                catch(ServerErrorException $ex4){
                    echo 'ServerErrorException !!'.PHP_EOL;
                    Log::error($ex4);
                }
                catch(DBALException $ex5){
                    echo 'DBALException !!'.PHP_EOL;
                    // db error
                   /* if(!is_null($sync_info) && $request->getType() == AbstractCalendarSyncWorkRequest::TypeAdd){

                        file_put_contents
                        (
                            $failed_tx_filename,
                            $sync_info->toJson()
                        );
                    }*/
                    Log::error($ex5);
                }
                catch(\Exception $ex6){
                    echo 'Exception !!'.PHP_EOL;
                    Log::error($ex6);
                }
            }
            return $count;
        });
    }
}