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
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\IAbstractCalendarSyncWorkRequestRepository;
use models\summit\SummitEvent;
use repositories\SilverStripeDoctrineRepository;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitEventCalendarSyncWorkRequest;
use utils\PagingInfo;
use utils\PagingResponse;

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
     * @param string $type
     * @return AbstractCalendarSyncWorkRequest
     */
    public function getUnprocessedMemberScheduleWorkRequest($member, $event, $type)
    {
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(MemberScheduleSummitEventCalendarSyncWorkRequest::class, "r")
            ->join('r.event', 'e', Join::WITH, " e.id = :event_id")
            ->join('r.owner', 'o', Join::WITH, " o.id = :member_id")
            ->where('r.type = :type')
            ->setParameter('event_id', $event->getId())
            ->setParameter('member_id', $member->getId())
            ->setParameter('type', $type)->getQuery();

        return $query->getSingleResult();
    }

    /**
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getUnprocessedMemberScheduleWorkRequestActionByPage(PagingInfo $paging_info){

        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("r")
            ->from(MemberScheduleSummitEventCalendarSyncWorkRequest::class, "r")
            ->where('(r.type = :type_add or r.type = :type_remove) and r.is_processed = 0')
            ->orderBy('r.created', 'ASC')
            ->setParameter('type_add',AbstractCalendarSyncWorkRequest::TypeAdd )
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