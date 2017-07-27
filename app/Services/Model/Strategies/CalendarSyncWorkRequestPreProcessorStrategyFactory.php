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
use App\Services\Model\Strategies\AdminActions\AdminSummitEventActionSyncWorkRequestDeleteStrategy;
use App\Services\Model\Strategies\AdminActions\AdminSummitEventActionSyncWorkRequestUpdateStrategy;
use App\Services\Model\Strategies\AdminActions\AdminSummitLocationActionSyncWorkRequestDeleteStrategy;
use App\Services\Model\Strategies\AdminActions\AdminSummitLocationActionSyncWorkRequestUpdateStrategy;
use App\Services\Model\Strategies\MemberActions\MemberCalendarScheduleSummitActionSyncWorkRequestAddStrategy;
use App\Services\Model\Strategies\MemberActions\MemberCalendarScheduleSummitActionSyncWorkRequestDeleteStrategy;
use App\Services\Model\Strategies\MemberActions\MemberEventScheduleSummitActionSyncWorkRequestAddStrategy;
use App\Services\Model\Strategies\MemberActions\MemberEventScheduleSummitActionSyncWorkRequestDeleteStrategy;
use App\Services\Model\Strategies\MemberActions\MemberEventScheduleSummitActionSyncWorkRequestUpdateStrategy;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
/**
 * Class CalendarSyncWorkRequestPreProcessorStrategyFactory
 * @package App\Services\Model\Strategies
 */
final class CalendarSyncWorkRequestPreProcessorStrategyFactory
implements ICalendarSyncWorkRequestPreProcessorStrategyFactory
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
     * CalendarSyncWorkRequestPreProcessorStrategyFactory constructor.
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncInfoRepository $calendar_sync_repository
     */
    public function __construct
    (
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncInfoRepository $calendar_sync_repository
    )
    {
        $this->work_request_repository  = $work_request_repository;
        $this->calendar_sync_repository = $calendar_sync_repository;
    }

    /**
     * @param ICalendarSyncWorkRequestQueueManager $queue_manager
     * @param AbstractCalendarSyncWorkRequest $request
     * @return ICalendarSyncWorkRequestPreProcessorStrategy|null
     */
    public function build(ICalendarSyncWorkRequestQueueManager $queue_manager, AbstractCalendarSyncWorkRequest $request){
        if($request instanceof MemberEventScheduleSummitActionSyncWorkRequest) {
            switch ($request->getType()) {
                case AbstractCalendarSyncWorkRequest::TypeRemove:
                    return new MemberEventScheduleSummitActionSyncWorkRequestDeleteStrategy($queue_manager, $this->work_request_repository);
                case AbstractCalendarSyncWorkRequest::TypeAdd:
                    return new MemberEventScheduleSummitActionSyncWorkRequestAddStrategy($queue_manager, $this->work_request_repository);

                case AbstractCalendarSyncWorkRequest::TypeUpdate:
                    return new MemberEventScheduleSummitActionSyncWorkRequestUpdateStrategy($queue_manager, $this->work_request_repository);
            }
        }
        if($request instanceof MemberCalendarScheduleSummitActionSyncWorkRequest){
            switch ($request->getType()) {
                case AbstractCalendarSyncWorkRequest::TypeRemove:
                    return new MemberCalendarScheduleSummitActionSyncWorkRequestDeleteStrategy
                    (
                        $queue_manager,
                        $this->work_request_repository,
                        $this->calendar_sync_repository
                    );

                case AbstractCalendarSyncWorkRequest::TypeAdd:
                    return new MemberCalendarScheduleSummitActionSyncWorkRequestAddStrategy();
            }
        }
        if($request instanceof AdminSummitEventActionSyncWorkRequest){
            switch ($request->getType()) {
                case AbstractCalendarSyncWorkRequest::TypeRemove:
                    return new AdminSummitEventActionSyncWorkRequestDeleteStrategy(
                        $queue_manager,
                        $this->work_request_repository
                    );
                case AbstractCalendarSyncWorkRequest::TypeUpdate:
                    return new AdminSummitEventActionSyncWorkRequestUpdateStrategy(
                        $queue_manager,
                        $this->work_request_repository
                    );
            }
        }
        if($request instanceof AdminSummitLocationActionSyncWorkRequest){
            switch ($request->getType()) {
                case AbstractCalendarSyncWorkRequest::TypeRemove:
                    return new AdminSummitLocationActionSyncWorkRequestDeleteStrategy(
                        $queue_manager,
                        $this->work_request_repository
                    );
                case AbstractCalendarSyncWorkRequest::TypeUpdate:
                    return new AdminSummitLocationActionSyncWorkRequestUpdateStrategy(
                        $queue_manager,
                        $this->work_request_repository
                    );
            }
        }

        return null;
    }
}