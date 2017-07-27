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

use Doctrine\DBAL\DBALException;
use libs\utils\ITransactionService;
use models\summit\CalendarSync\WorkQueue\AdminScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use models\summit\IScheduleCalendarSyncInfoRepository;
use utils\PagingInfo;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class AdminActionsCalendarSyncProcessingService
 * @package services\model
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
     * MemberActionsCalendarSyncProcessingService constructor.
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncInfoRepository $calendar_sync_repository
     * @param IScheduleCalendarSyncInfoRepository $schedule_sync_repository
     * @param ITransactionService $tx_manager
     */
    public function __construct
    (
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncInfoRepository $calendar_sync_repository,
        IScheduleCalendarSyncInfoRepository $schedule_sync_repository,
        ITransactionService $tx_manager
    )
    {
        $this->work_request_repository   = $work_request_repository;
        $this->calendar_sync_repository  = $calendar_sync_repository;
        $this->schedule_sync_repository = $schedule_sync_repository;
        $this->tx_manager                = $tx_manager;
    }


    /**
     * @param int $batch_size
     * @return int
     */
    public function processActions($batch_size = 100)
    {
        return $this->tx_manager->transaction(function() use($batch_size){
            $count = 0;
            $res = $this->work_request_repository->getUnprocessedAdminScheduleWorkRequestActionByPage
            (
                new PagingInfo(1, $batch_size)
            );

            foreach ($res->getItems() as $request){

                try {
                    if (!$request instanceof AdminScheduleSummitActionSyncWorkRequest) continue;

                    if($request instanceof AdminSummitEventActionSyncWorkRequest){

                        $has_more     = true;
                        $page         = 1;
                        $summit_event = $request->getSummitEvent();
                        do{
                            $page_response = $this->schedule_sync_repository->getAllBySummitEvent($summit_event, new PagingInfo($page, 1000));

                            foreach ($page_response->getItems() as $schedule_events){
                               switch($request->getType()){

                               }

                                $work_request = new MemberEventScheduleSummitActionSyncWorkRequest();
                                $work_request->setCalendarSyncInfo($request->getCalendarSyncInfo());
                                $work_request->setSummitEvent($summit_event);
                            }

                        }while($has_more);
                    }

                    if($request instanceof AdminSummitLocationActionSyncWorkRequest){

                    }

                    $request->markProcessed();
                    $count++;
                }
                catch(DBALException $ex1){
                    echo 'DBALException !!'.PHP_EOL;
                    // db error
                    /* if(!is_null($sync_info) && $request->getType() == AbstractCalendarSyncWorkRequest::TypeAdd){

                         file_put_contents
                         (
                             $failed_tx_filename,
                             $sync_info->toJson()
                         );
                     }*/
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