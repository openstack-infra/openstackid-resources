<?php namespace App\Services\Model\Strategies\MemberActions;

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

use App\Services\Model\Strategies\ICalendarSyncWorkRequestPreProcessorStrategy;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use App\Services\Model\MemberScheduleWorkQueueManager;

/**
 * Class MemberCalendarScheduleSummitActionSyncWorkRequestDeleteStrategy
 * @package App\Services\Model\Strategies\MemberActions
 */
final class MemberCalendarScheduleSummitActionSyncWorkRequestDeleteStrategy
    implements ICalendarSyncWorkRequestPreProcessorStrategy
{
    /**
     * @var MemberScheduleWorkQueueManager
     */
    private $queue_manager;

    /**
     * @var IAbstractCalendarSyncWorkRequestRepository
     */
    private $work_request_repository;

    /**
     * @var ICalendarSyncInfoRepository
     */
    private $calendar_sync_repository;

    /**
     * MemberCalendarScheduleSummitActionSyncWorkRequestDeleteStrategy constructor.
     * @param MemberScheduleWorkQueueManager $queue_manager
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncInfoRepository $calendar_sync_repository
     */
    public function __construct
    (
        MemberScheduleWorkQueueManager $queue_manager,
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncInfoRepository $calendar_sync_repository
    )
    {
        $this->queue_manager            = $queue_manager;
        $this->work_request_repository  = $work_request_repository;
        $this->calendar_sync_repository = $calendar_sync_repository;
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return AbstractCalendarSyncWorkRequest|null
     */
    public function process(AbstractCalendarSyncWorkRequest $request)
    {
        if(! $request instanceof MemberCalendarScheduleSummitActionSyncWorkRequest) return null;
        $calendar_sync_info   = $request->getCalendarSyncInfo();
        //check if we have pending make calendar on this round ...
        $pending_calendar_create_request = $this->queue_manager->getCalendarRequestFor($calendar_sync_info->getId(), AbstractCalendarSyncWorkRequest::TypeAdd);
        $calendar_created = true;
        if(!is_null($pending_calendar_create_request)){
            if($this->queue_manager->removeRequest($pending_calendar_create_request))
                $this->work_request_repository->delete($pending_calendar_create_request);
            $calendar_created = false;
        }
        // delete all pending work ( calendar and events)
        $pending_requests = $this->queue_manager->getPendingEventsForCalendar($calendar_sync_info->getId());
        if(count($pending_requests) > 0 ) {
            foreach($pending_requests as $pending_request) {
                if($this->queue_manager->removeRequest($pending_request))
                    $this->work_request_repository->delete($pending_request);
            }
        }

        if(!$calendar_created){
            // delete the current request ( delete calendar, we never created it )
            $this->work_request_repository->delete($request);
            $this->queue_manager->clearPendingEventsForCalendar($calendar_sync_info->getId());
            // delete revoked credentials;
            $this->calendar_sync_repository->delete($calendar_sync_info);
            return null;
        }

        return $request;
    }
}