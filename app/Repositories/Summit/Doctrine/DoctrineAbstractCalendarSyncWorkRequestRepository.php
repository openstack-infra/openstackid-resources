<?php namespace repositories\summit;
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

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\main\Member;
use models\summit\CalendarSync\CalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminScheduleSummitActionSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\SummitEvent;
use repositories\SilverStripeDoctrineRepository;
use utils\PagingInfo;
use utils\PagingResponse;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitActionSyncWorkRequest;
use Illuminate\Support\Facades\Log;

/**
 * Class DoctrineAbstractCalendarSyncWorkRequestRepository
 * @package repositories\summit
 */
final class DoctrineAbstractCalendarSyncWorkRequestRepository
    extends SilverStripeDoctrineRepository
    implements IAbstractCalendarSyncWorkRequestRepository
{

    /**
     * @param Member $member
     * @param SummitEvent $event
     * @param CalendarSyncInfo $calendar_sync_info
     * @param string|null $type
     * @return AbstractCalendarSyncWorkRequest
     */
    public function getUnprocessedMemberScheduleWorkRequest($member, $event, $calendar_sync_info, $type = null)
    {
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(MemberEventScheduleSummitActionSyncWorkRequest::class, "r")
            ->join('r.summit_event', 'e', Join::WITH, " e.id = :event_id")
            ->join('r.owner', 'o', Join::WITH, " o.id = :member_id")
            ->join('r.calendar_sync_info', 'si', Join::WITH, " si.id = :calendar_sync_info_id");
        if(!empty($type)){
            $query = $query
                ->where('r.type = :type')
                ->setParameter('type', $type)->getQuery();
        }

        $query
            ->setParameter('event_id', $event->getId())
            ->setParameter('member_id', $member->getId())
            ->setParameter('calendar_sync_info_id', $calendar_sync_info->getId());

        return $query->getSingleResult();
    }

    /**
     * @param string $provider
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getUnprocessedMemberScheduleWorkRequestActionByPage($provider = 'ALL', PagingInfo $paging_info){

        log::debug(sprintf("DoctrineAbstractCalendarSyncWorkRequestRepository::getUnprocessedMemberScheduleWorkRequestActionByPage: provider %s",$provider));
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(MemberScheduleSummitActionSyncWorkRequest::class, "r")
            ->where('(r.type = :type_add or r.type = :type_remove or r.type = :type_update) and r.is_processed = 0')
            ->orderBy('r.id', 'ASC')
            ->setParameter('type_add',AbstractCalendarSyncWorkRequest::TypeAdd )
            ->setParameter('type_update',AbstractCalendarSyncWorkRequest::TypeUpdate )
            ->setParameter('type_remove',AbstractCalendarSyncWorkRequest::TypeRemove);

        if(CalendarSyncInfo::isValidProvider($provider)){
            log::debug(sprintf("provider %s is valid",$provider));
            $query
                ->join('r.calendar_sync_info', 'si', Join::WITH, " si.provider = :provider")
                ->setParameter('provider', $provider);
        }
        $query= $query
            ->setFirstResult($paging_info->getOffset())
            ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = [];

        foreach($paginator as $entity)
            $data[] = $entity;

        return new PagingResponse
        (
            $total,
            $paging_info->getPerPage(),
            $paging_info->getCurrentPage(),
            $paging_info->getLastPage($total),
            $data
        );
    }

    /**
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getUnprocessedAdminScheduleWorkRequestActionByPage(PagingInfo $paging_info){
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(AdminScheduleSummitActionSyncWorkRequest::class, "r")
            ->where('(r.type = :type_add or r.type = :type_remove or r.type = :type_update) and r.is_processed = 0')
            ->orderBy('r.id', 'ASC')
            ->setParameter('type_add',AbstractCalendarSyncWorkRequest::TypeAdd )
            ->setParameter('type_update',AbstractCalendarSyncWorkRequest::TypeUpdate )
            ->setParameter('type_remove',AbstractCalendarSyncWorkRequest::TypeRemove);

        $query= $query
            ->setFirstResult($paging_info->getOffset())
            ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = [];

        foreach($paginator as $entity)
            $data[] = $entity;

        return new PagingResponse
        (
            $total,
            $paging_info->getPerPage(),
            $paging_info->getCurrentPage(),
            $paging_info->getLastPage($total),
            $data
        );
    }
}