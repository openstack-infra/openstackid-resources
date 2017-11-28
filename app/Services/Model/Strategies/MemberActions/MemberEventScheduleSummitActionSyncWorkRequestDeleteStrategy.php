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
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use App\Services\Model\MemberScheduleWorkQueueManager;

/**
 * Class MemberEventScheduleSummitActionSyncWorkRequestDeleteStrategy
 * @package App\Services\Model\Strategies\MemberActions
 */
final class MemberEventScheduleSummitActionSyncWorkRequestDeleteStrategy
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
     * MemberEventScheduleSummitActionSyncWorkRequestDeleteStrategy constructor.
     * @param MemberScheduleWorkQueueManager $queue_manager
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     */
    public function __construct
    (
        MemberScheduleWorkQueueManager $queue_manager,
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository
    )
    {
        $this->queue_manager           = $queue_manager;
        $this->work_request_repository = $work_request_repository;
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return AbstractCalendarSyncWorkRequest|null
     */
    public function process(AbstractCalendarSyncWorkRequest $request)
    {
        if(!$request instanceof MemberEventScheduleSummitActionSyncWorkRequest) return null;
        $summit_event_id      = $request->getSummitEventId();
        $calendar_sync_info   = $request->getCalendarSyncInfo();
        // check if there is a former add, disregard and omit
        $pending_requests = $this->queue_manager->getSummitEventRequestFor($calendar_sync_info->getId(), $summit_event_id);
        if(count($pending_requests) > 0 ) {
            foreach ($pending_requests as $pending_request) {
                if($request->getType() == AbstractCalendarSyncWorkRequest::TypeUpdate)
                {
                    $this->queue_manager->registerRequestForDelete($request);
                    continue;
                }
                if($this->queue_manager->removeRequest($pending_request))
                    $this->work_request_repository->delete($pending_request);
            }
            // if the event is not already synchronized disregard delete
            if(!$request->getOwner()->isEventSynchronized($calendar_sync_info, $summit_event_id)) {
                $this->work_request_repository->delete($request);
                return null;
            }
        }
        return $request;
    }
}