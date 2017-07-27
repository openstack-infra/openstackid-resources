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

use CalDAVClient\Facade\Exceptions\ForbiddenException;
use CalDAVClient\Facade\Exceptions\NotFoundResourceException;
use CalDAVClient\Facade\Exceptions\ServerErrorException;
use CalDAVClient\Facade\Exceptions\UserUnAuthorizedException;
use Doctrine\DBAL\DBALException;
use libs\utils\ITransactionService;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\ICalendarSyncInfoRepository;
use services\apis\CalendarSync\CalendarSyncRemoteFacadeFactory;
use utils\PagingInfo;
use Illuminate\Support\Facades\Log;

/**
 * Class MemberActionsCalendarSyncProcessingService
 * @package services\model
 */
final class MemberActionsCalendarSyncProcessingService
implements IMemberActionsCalendarSyncProcessingService
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
     * @var ITransactionService
     */
    private $tx_manager;

    /**
     * MemberActionsCalendarSyncProcessingService constructor.
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param ICalendarSyncInfoRepository $calendar_sync_repository
     * @param ITransactionService $tx_manager
     */
    public function __construct
    (
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        ICalendarSyncInfoRepository $calendar_sync_repository,
        ITransactionService $tx_manager
    )
    {
        $this->work_request_repository  = $work_request_repository;
        $this->calendar_sync_repository = $calendar_sync_repository;
        $this->tx_manager               = $tx_manager;
    }

    /**
     * @param int $batch_size
     * @return int
     */
    public function processActions($batch_size = 100)
    {
        return $this->tx_manager->transaction(function() use($batch_size){
            $count = 0;
            $res = $this->work_request_repository->getUnprocessedMemberScheduleWorkRequestActionByPage
            (
                new PagingInfo(1, $batch_size)
            );

            foreach ($res->getItems() as $request){

                try {
                    if (!$request instanceof MemberScheduleSummitActionSyncWorkRequest) continue;
                    $calendar_sync_info   = $request->getCalendarSyncInfo();
                    $remote_facade        = CalendarSyncRemoteFacadeFactory::getInstance()->build($calendar_sync_info);
                    if (is_null($remote_facade)) continue;
                    $member               = $request->getOwner();
                    $request_type         = $request->getType();
                    $request_sub_type     = $request->getSubType();

                    echo sprintf
                    (
                        "processing work request %s - sub type %s - type %s - member %s - revoked credentials %s",
                                $request->getIdentifier(),
                                $request_sub_type,
                                $request_type,
                                $member->getIdentifier(),
                                $calendar_sync_info->isRevoked()? 1:0
                    ).PHP_EOL;

                    switch ($request_sub_type) {

                        case MemberEventScheduleSummitActionSyncWorkRequest::SubType: {
                            $summit_event = $request->getSummitEvent();
                            switch ($request_type) {
                                case AbstractCalendarSyncWorkRequest::TypeAdd:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $member->add2ScheduleSyncInfo($remote_facade->addEvent($request));
                                    break;
                                case AbstractCalendarSyncWorkRequest::TypeUpdate:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $sync_info    = $member->getScheduleSyncInfoByEvent($summit_event, $calendar_sync_info);
                                    $is_scheduled = $member->isOnSchedule($summit_event);
                                    if(!is_null($sync_info)) {
                                        if(!$is_scheduled)
                                            $member->removeFromScheduleSyncInfo($sync_info);
                                        else
                                            $remote_facade->updateEvent($request, $sync_info);
                                    }
                                    break;
                                case AbstractCalendarSyncWorkRequest::TypeRemove:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $sync_info = $member->getScheduleSyncInfoByEvent($summit_event, $calendar_sync_info);
                                    $remote_facade->deleteEvent($request, $sync_info);
                                    $member->removeFromScheduleSyncInfo($sync_info);
                                    break;
                            }
                        }
                        break;
                        case MemberCalendarScheduleSummitActionSyncWorkRequest::SubType: {
                            switch ($request_type) {
                                case AbstractCalendarSyncWorkRequest::TypeAdd:
                                    if($calendar_sync_info->isRevoked()) continue;
                                    $remote_facade->createCalendar($request, $calendar_sync_info);
                                    break;
                                case AbstractCalendarSyncWorkRequest::TypeRemove:
                                    $remote_facade->deleteCalendar($request, $calendar_sync_info);
                                    $member->removeFromCalendarSyncInfo($calendar_sync_info);
                                    break;
                            }
                        }
                        break;
                    }

                    $request->markProcessed();
                    $count++;
                }
                catch(ForbiddenException $ex1){
                    // cant create calendar ...
                    echo 'ForbiddenException !!'.PHP_EOL;
                    Log::warning($ex1);
                }
                catch(UserUnAuthorizedException $ex2){
                    echo 'UserUnAuthorizedException !!'.PHP_EOL;
                    Log::warning($ex2);
                }
                catch(NotFoundResourceException $ex3){
                    echo 'NotFoundResourceException !!'.PHP_EOL;
                    Log::error($ex3);
                }
                catch(ServerErrorException $ex4){
                    echo 'ServerErrorException !!'.PHP_EOL;
                    Log::error($ex4);
                }
                catch(DBALException $ex5){
                    echo 'DBALException !!'.PHP_EOL;
                    // db error
                   /* if(!is_null($sync_info) && $request->getType() == AbstractCalendarSyncWorkRequest::TypeAdd){

                        file_put_contents
                        (
                            $failed_tx_filename,
                            $sync_info->toJson()
                        );
                    }*/
                    Log::error($ex5);
                }
                catch(\Exception $ex6){
                    echo 'Exception !!'.PHP_EOL;
                    Log::error($ex6);
                }
            }
            return $count;
        });
    }
}