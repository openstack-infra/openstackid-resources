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

use App\Events\MyScheduleRemove;
use CalDAVClient\Facade\Exceptions\ForbiddenException;
use CalDAVClient\Facade\Exceptions\NotFoundResourceException;
use CalDAVClient\Facade\Exceptions\ServerErrorException;
use CalDAVClient\Facade\Exceptions\UserUnAuthorizedException;
use Doctrine\DBAL\DBALException;
use Illuminate\Support\Facades\Event;
use libs\utils\ITransactionService;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitEventCalendarSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\IRSVPRepository;
use services\apis\CalendarSync\CalendarSyncRemoteFacadeFactory;
use utils\PagingInfo;

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
     * @var IRSVPRepository
     */
    private $rsvp_repository;

    /**
     * @var ITransactionService
     */
    private $tx_manager;

    /**
     * MemberActionsCalendarSyncProcessingService constructor.
     * @param IAbstractCalendarSyncWorkRequestRepository $work_request_repository
     * @param IRSVPRepository $rsvp_repository
     * @param ITransactionService $tx_manager
     */
    public function __construct
    (
        IAbstractCalendarSyncWorkRequestRepository $work_request_repository,
        IRSVPRepository $rsvp_repository,
        ITransactionService $tx_manager
    )
    {
        $this->work_request_repository = $work_request_repository;
        $this->rsvp_repository         = $rsvp_repository;
        $this->tx_manager              = $tx_manager;
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
                    if (!$request instanceof MemberScheduleSummitEventCalendarSyncWorkRequest) continue;
                    $event   = $request->getEvent();
                    $member  = $request->getOwner();
                    $failed_tx_filename = sprintf
                    (
                        '/tmp/failed_process_%s_%s.json',
                        $member->getIdentifier(),
                        $event->getIdentifier()
                    );
                    if (!$event->isPublished() && $request->getType() == AbstractCalendarSyncWorkRequest::TypeAdd) {
                        if ($member->isOnSchedule($event)) {
                            // event is not published anymore at this time, so, remove from user schedule and delete request
                            // we should check if has rsvp
                            $rsvp = $member->getRsvpByEvent($event->getIdentifier());
                            if (!is_null($rsvp)) {
                                $this->rsvp_repository->delete($rsvp);
                            }

                            $member->removeFromSchedule($event);
                            Event::fire(new MyScheduleRemove($member, $event->getSummit(), $event->getIdentifier()));
                        }
                        $this->work_request_repository->delete($request);
                        continue;
                    }

                    $remote_facade = CalendarSyncRemoteFacadeFactory::getInstance()->build($request->getCalendar());

                    if (is_null($remote_facade)) continue;

                    switch ($request->getType()) {
                        case AbstractCalendarSyncWorkRequest::TypeAdd:
                            if(file_exists($failed_tx_filename)){
                                $sync_info = ScheduleCalendarSyncInfo::buildFromJson(file_get_contents($failed_tx_filename));
                                $sync_info->setSyncInfo($request->getCalendar());
                                $sync_info->setEvent($event);
                                $sync_info->setLocation($event->getLocation());
                            }
                            else {
                                $sync_info = $remote_facade->addEvent($request);
                            }
                            $member->add2ScheduleSyncInfo($sync_info);
                            break;
                        case AbstractCalendarSyncWorkRequest::TypeRemove:
                            $sync_info = $member->getScheduleSyncInfoByEvent($event);
                            $remote_facade->deleteEvent($request, $sync_info);
                            $member->removeFromScheduleSyncInfo($sync_info);
                            break;
                    }

                    $request->markProcessed();
                    $count++;
                }
                catch(ForbiddenException $ex1){
                    // cant create calendar ...
                }
                catch(UserUnAuthorizedException $ex2){

                }
                catch(NotFoundResourceException $ex3){

                }
                catch(ServerErrorException $ex4){

                }
                catch(DBALException $ex5){
                    // db error
                    if(!is_null($sync_info) && $request->getType() == AbstractCalendarSyncWorkRequest::TypeAdd){

                        file_put_contents
                        (
                            $failed_tx_filename,
                            $sync_info->toJson()
                        );
                    }
                }
                catch(\Exception $ex6){

                }
            }
            return $count;
        });
    }
}