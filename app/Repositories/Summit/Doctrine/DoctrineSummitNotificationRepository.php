<?php namespace repositories\summit;
/**
 * Copyright 2016 OpenStack Foundation
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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\ISummitNotificationRepository;
use models\summit\Summit;
use models\summit\SummitPushNotification;
use repositories\SilverStripeDoctrineRepository;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class DoctrineSummitNotificationRepository
 * @package repositories\summit
 */
final class DoctrineSummitNotificationRepository
    extends SilverStripeDoctrineRepository
    implements ISummitNotificationRepository
{


    /**
     * @param Summit $summit
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getAllByPage(Summit $summit, PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select("n")
            ->from(SummitPushNotification::class, "n")
            ->leftJoin('n.summit_event', 'e')
            ->join('n.summit', 's', Join::WITH, " s.id = :summit_id")
            ->setParameter('summit_id', $summit->getId());

        if (!is_null($filter)) {

            $filter->apply2Query($query, array
            (
                'event_id'  => 'e.id:json_int',
                'channel'   => 'n.channel:json_string',
                'sent_date' => 'n.sent_date:datetime_epoch',
                'created'   => 'n.created:datetime_epoch',
                'is_sent'   => 'n.is_sent:json_int',
            ));
        }

        if (!is_null($order)) {

            $order->apply2Query($query, array
            (
                'sent_date' => 'n.sent_date',
                'created'   => 'n.created',
                'id'        => 'n.id',
            ));
        } else {
            //default order
            $query = $query->orderBy('n.id', Criteria::DESC);
        }

        $query = $query
            ->setFirstResult($paging_info->getOffset())
            ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = array();

        foreach ($paginator as $entity)
            array_push($data, $entity);

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