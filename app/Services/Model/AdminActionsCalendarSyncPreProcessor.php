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

use App\Services\Model\Strategies\ICalendarSyncWorkRequestPreProcessorStrategyFactory;

/**
 * Class AdminActionsCalendarSyncPreProcessor
 * @package App\Services\Model
 */
final class AdminActionsCalendarSyncPreProcessor
    implements ICalendarSyncWorkRequestPreProcessor
{

    /**
     * @var ICalendarSyncWorkRequestQueueManager
     */
    private $queue_manager;


    /**
     * @var ICalendarSyncWorkRequestPreProcessorStrategyFactory
     */
    private $strategy_factory;

    /**
     * AdminActionsCalendarSyncPreProcessor constructor.
     * @param ICalendarSyncWorkRequestQueueManager $queue_manager
     * @param ICalendarSyncWorkRequestPreProcessorStrategyFactory $strategy_factory
     */
    public function __construct
    (
        ICalendarSyncWorkRequestQueueManager $queue_manager,
        ICalendarSyncWorkRequestPreProcessorStrategyFactory $strategy_factory
    )
    {
        $this->queue_manager = $queue_manager;
        $this->strategy_factory = $strategy_factory;
    }

    /**
     * @param array $requests
     * @return array
     */
    public function preProcessActions(array $requests)
    {
        foreach ($requests as $request){
            $strategy = $this->strategy_factory->build($this->queue_manager, $request);
            if(is_null($strategy)) continue;
            $request = $strategy->process($request);
            if(!is_null($request))
                $this->queue_manager->registerRequest($request);
        }

        return $this->queue_manager->getPurgedRequests();
    }
}