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
 * Class MemberEventScheduleSummitActionSyncWorkRequestAddStrategy
 * @package App\Services\Model\Strategies\MemberActions
 */
final class MemberEventScheduleSummitActionSyncWorkRequestAddStrategy
implements ICalendarSyncWorkRequestPreProcessorStrategy
{

    /**
     * @var MemberScheduleWorkQueueManager
     */
    private $work_queue_manager;

    /**
     * @var IAbstractCalendarSyncWorkRequestRepository
     */
    private $work_request_repository;

    /**
     * MemberEventScheduleSummitActionSyncWorkRequestAddStrategy constructor.
     * @param MemberScheduleWorkQueueManager $work_queue_manager
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     */
    public function __construct
    (
        MemberScheduleWorkQueueManager $work_queue_manager,
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository
    )
    {
        $this->work_queue_manager      = $work_queue_manager;
        $this->work_request_repository = $work_request_repository;
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return AbstractCalendarSyncWorkRequest|null
     */
    public function process(AbstractCalendarSyncWorkRequest $request)
    {
        if(!$request instanceof MemberEventScheduleSummitActionSyncWorkRequest) return null;
        $summit_event         = $request->getSummitEvent();
        $calendar_sync_info   = $request->getCalendarSyncInfo();
        // check if there is a former add, disregard and omit
        $pending_requests     = $this->work_queue_manager->getSummitEventRequestFor($calendar_sync_info->getId(), $summit_event->getId());
        if(count($pending_requests) > 0 ) {
            foreach ($pending_requests as $pending_request) {
                if($request->getType() == AbstractCalendarSyncWorkRequest::TypeUpdate)
                {
                    $this->work_queue_manager->registerRequestForDelete($request);
                    continue;
                }
                if($this->work_queue_manager->removeRequest($pending_request))
                    $this->work_request_repository->delete($pending_request);
            }
            // if the event is not already synchronized disregard add
            if($request->getOwner()->isEventSynchronized($calendar_sync_info, $summit_event)) {
                $this->work_queue_manager->unRegisterRequestForDelete($request, AbstractCalendarSyncWorkRequest::TypeUpdate);
                $this->work_request_repository->delete($request);
                return null;
            }
        }
        return $request;
    }
}