<?php namespace App\Services\Model;
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

use App\Services\Model\ICalendarSyncWorkRequestQueueManager;
use App\Services\Model\Strategies\ICalendarSyncWorkRequestPreProcessorStrategyFactory;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;

/**
 * Class MemberActionsCalendarSyncPreProcessor
 * @package services\model
 */
final class MemberActionsCalendarSyncPreProcessor
    implements ICalendarSyncWorkRequestPreProcessor
{
    /**
     * @var ICalendarSyncWorkRequestQueueManager
     */
    private $queue_manager;

    /**
     * @var IAbstractCalendarSyncWorkRequestRepository
     */
    private $work_request_repository;

    /**
     * @var ICalendarSyncWorkRequestPreProcessorStrategyFactory
     */
    private $strategy_factory;

    /**
     * MemberActionsCalendarSyncPreProcessor constructor.
     * @param ICalendarSyncWorkRequestQueueManager $queue_manager
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncWorkRequestPreProcessorStrategyFactory $strategy_factory
     */
    public function __construct
    (
        ICalendarSyncWorkRequestQueueManager $queue_manager,
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncWorkRequestPreProcessorStrategyFactory $strategy_factory
    )
    {
        $this->queue_manager           = $queue_manager;
        $this->work_request_repository = $work_request_repository;
        $this->strategy_factory        = $strategy_factory;
    }

    /**
     * @param MemberScheduleSummitActionSyncWorkRequest[] $requests
     * @return MemberScheduleSummitActionSyncWorkRequest[]
     */
    function preProcessActions(array $requests){

        foreach ($requests as $request){
            $strategy = $this->strategy_factory->build($this->queue_manager, $request);
            if(is_null($strategy)) continue;
            $request = $strategy->process($request);
            if(!is_null($request))
                $this->queue_manager->registerRequest($request);
        }

        foreach($this->queue_manager->getRequestsToDelete() as $request_2_delete){
            $this->work_request_repository->delete($request_2_delete);
        }
        return $this->queue_manager->getPurgedRequests();
    }
}