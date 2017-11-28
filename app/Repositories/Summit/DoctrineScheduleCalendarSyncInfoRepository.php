<?php namespace App\Repositories\Summit;

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
use models\summit\IScheduleCalendarSyncInfoRepository;
use models\summit\SummitAbstractLocation;
use models\summit\SummitEvent;
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\SummitEventFeedback;
use App\Repositories\SilverStripeDoctrineRepository;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class DoctrineScheduleCalendarSyncInfoRepository
 * @package App\Repositories\Summit
 */
final class DoctrineScheduleCalendarSyncInfoRepository
    extends SilverStripeDoctrineRepository
    implements IScheduleCalendarSyncInfoRepository
{

    /**
     * @param int $summit_event_id
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getAllBySummitEvent($summit_event_id, PagingInfo $paging_info)
    {
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("si")
            ->from(ScheduleCalendarSyncInfo::class, "si")
            ->join('si.calendar_sync_info', 'ci', Join::WITH, " ci.revoked = :credential_status")
            ->where("si.summit_event_id = :event_id")
            ->orderBy('si.id', 'ASC')
            ->setParameter('event_id', $summit_event_id)
            ->setParameter('credential_status',false);

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
     * @param SummitAbstractLocation $location
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getAllBySummitLocation(SummitAbstractLocation $location, PagingInfo $paging_info)
    {
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("si")
            ->from(ScheduleCalendarSyncInfo::class, "si")
            ->join('si.location', 'l', Join::WITH, " l.id = :location_id")
            ->join('si.calendar_sync_info', 'ci', Join::WITH, " ci.revoked = :crendential_status")
            ->orderBy('si.id', 'ASC')
            ->setParameter('location_id', $location->getId())
            ->setParameter('crendential_status',false);

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
     * @return string
     */
    protected function getBaseEntity()
    {
       return SummitEventFeedback::class;
    }
}