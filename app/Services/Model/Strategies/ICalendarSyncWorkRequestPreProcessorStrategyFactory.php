<?php namespace App\Services\Model\Strategies;
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
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;

/**
 * Interface ICalendarSyncWorkRequestPreProcessorStrategyFactory
 * @package App\Services\Model\Strategies
 */
interface ICalendarSyncWorkRequestPreProcessorStrategyFactory
{
    /**
     * @param ICalendarSyncWorkRequestQueueManager   $queue_manager
     * @param AbstractCalendarSyncWorkRequest $request
     * @return ICalendarSyncWorkRequestPreProcessorStrategy|null
     */
    public function build(ICalendarSyncWorkRequestQueueManager $queue_manager, AbstractCalendarSyncWorkRequest $request);
}