<?php namespace App\Services\Model\Strategies\AdminActions;
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

use App\Services\Model\AdminScheduleWorkQueueManager;
use App\Services\Model\Strategies\ICalendarSyncWorkRequestPreProcessorStrategy;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;

/**
 * Class AdminSummitLocationActionSyncWorkRequestDeleteStrategy
 * @package App\Services\Model\Strategies\AdminActions
 */
final class AdminSummitLocationActionSyncWorkRequestDeleteStrategy
    implements ICalendarSyncWorkRequestPreProcessorStrategy
{
    /**
     * @var AdminScheduleWorkQueueManager
     */
    private $queue_manager;

    /**
     * @var IAbstractCalendarSyncWorkRequestRepository
     */
    private $work_request_repository;

    /**
     * AdminSummitLocationActionSyncWorkRequestDeleteStrategy constructor.
     * @param AdminScheduleWorkQueueManager $queue_manager
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     */
    public function __construct
    (
        AdminScheduleWorkQueueManager $queue_manager,
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository
    )
    {
        $this->queue_manager = $queue_manager;
        $this->work_request_repository = $work_request_repository;
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return AbstractCalendarSyncWorkRequest|null
     */
    public function process(AbstractCalendarSyncWorkRequest $request)
    {
        if(!$request instanceof AdminSummitLocationActionSyncWorkRequest) return null;
        $location = $request->getLocation();
        $pending_requests = $this->queue_manager->getSummitLocationRequestFor($location->getId());
        if(count($pending_requests) > 0 ){
            // delete all former and pending  ...
            foreach ($pending_requests as $pending_request) {
                if($this->queue_manager->removeRequest($pending_request))
                    $this->work_request_repository->delete($pending_request);
            }
        }
        return $request;
    }
}