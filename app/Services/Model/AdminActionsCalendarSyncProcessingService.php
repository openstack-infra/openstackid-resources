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
use libs\utils\ITransactionService;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use models\summit\IScheduleCalendarSyncInfoRepository;
use utils\PagingInfo;
use Doctrine\DBAL\DBALException;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class AdminActionsCalendarSyncProcessingService
 * @package App\Services\Model
 */
final class AdminActionsCalendarSyncProcessingService
    implements IAdminActionsCalendarSyncProcessingService
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
     * @var IScheduleCalendarSyncInfoRepository
     */
    private $schedule_sync_repository;

    /**
     * @var ITransactionService
     */
    private $tx_manager;

    /**
     * @var ICalendarSyncWorkRequestPreProcessor
     */
    private $preprocessor_requests;

    /**
     * AdminActionsCalendarSyncProcessingService constructor.
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncInfoRepository $calendar_sync_repository
     * @param IScheduleCalendarSyncInfoRepository $schedule_sync_repository
     * @param ICalendarSyncWorkRequestPreProcessor $preprocessor_requests
     * @param ITransactionService $tx_manager
     */
    public function __construct
    (
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncInfoRepository $calendar_sync_repository,
        IScheduleCalendarSyncInfoRepository $schedule_sync_repository,
        ICalendarSyncWorkRequestPreProcessor $preprocessor_requests,
        ITransactionService $tx_manager
    )
    {
        $this->work_request_repository  = $work_request_repository;
        $this->calendar_sync_repository = $calendar_sync_repository;
        $this->schedule_sync_repository = $schedule_sync_repository;
        $this->preprocessor_requests    = $preprocessor_requests;
        $this->tx_manager               = $tx_manager;
    }

    /**
     * @param int $batch_size
     * @return int
     */
    public function processActions($batch_size = PHP_INT_MAX)
    {
        return $this->tx_manager->transaction(function() use($batch_size){
            $count = 0;

            $res = $this->work_request_repository->getUnprocessedAdminScheduleWorkRequestActionByPage
            (
                new PagingInfo(1, $batch_size)
            );

            foreach ($this->preprocessor_requests->preProcessActions($res->getItems()) as $request){

                try {
                    if (!$request instanceof AdminScheduleSummitActionSyncWorkRequest) continue;

                    if($request instanceof AdminSummitEventActionSyncWorkRequest){

                        $page         = 1;
                        $summit_event = $request->getSummitEvent();

                        do{
                            $page_response = $this->schedule_sync_repository->getAllBySummitEvent($summit_event, new PagingInfo($page, 1000));
                            $has_more      = count($page_response->getItems()) > 0;
                            if(!$has_more) continue;
                            foreach ($page_response->getItems() as $schedule_event){
                                if(!$schedule_event instanceof ScheduleCalendarSyncInfo) continue;
                                $work_request = new MemberEventScheduleSummitActionSyncWorkRequest();
                                $work_request->setType($request->getType());
                                $work_request->setCalendarSyncInfo($schedule_event->getCalendarSyncInfo());
                                $work_request->setOwner($schedule_event->getMember());
                                $work_request->setSummitEvent($summit_event);
                                $this->work_request_repository->add($work_request);
                            }
                            $page++;

                        }while($has_more);
                    }

                    if($request instanceof AdminSummitLocationActionSyncWorkRequest){
                        $location = $request->getLocation();
                        $page     = 1;

                        do{
                            $page_response = $this->schedule_sync_repository->getAllBySummitLocation($location, new PagingInfo($page, 1000));
                            $has_more      = count($page_response->getItems()) > 0;
                            if(!$has_more) continue;
                            foreach ($page_response->getItems() as $schedule_event){
                                if(!$schedule_event instanceof ScheduleCalendarSyncInfo) continue;
                                $work_request = new MemberEventScheduleSummitActionSyncWorkRequest();
                                // always is update no matter what
                                $work_request->setType(AbstractCalendarSyncWorkRequest::TypeUpdate);
                                $work_request->setCalendarSyncInfo($schedule_event->getCalendarSyncInfo());
                                $work_request->setOwner($schedule_event->getMember());
                                $work_request->setSummitEvent($summit_event);
                                $this->work_request_repository->add($work_request);
                            }
                            $page++;

                        }while($has_more);
                    }

                    $request->markProcessed();
                    $count++;
                }
                catch(DBALException $ex1){
                    echo 'DBALException !!'.PHP_EOL;
                    Log::error($ex1);
                }
                catch(Exception $ex6){
                    echo 'Exception !!'.PHP_EOL;
                    Log::error($ex6);
                }
            }
            return $count;
        });
    }
}