<?php namespace repositories\main;
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
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\main\IChatTeamPushNotificationMessageRepository;
use repositories\SilverStripeDoctrineRepository;
use utils\DoctrineJoinFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class DoctrineChatTeamPushNotificationMessageRepository
 * @package repositories\main
 */
final class DoctrineChatTeamPushNotificationMessageRepository
    extends SilverStripeDoctrineRepository
    implements IChatTeamPushNotificationMessageRepository
{

    /**
     * @param int $team_id
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    function getAllSentByTeamPaginated($team_id, PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {
        $query  = $this->getEntityManager()->createQueryBuilder()
            ->select("m")
            ->from(\models\main\ChatTeamPushNotificationMessage::class, "m")
            ->join('m.team', 't')
            ->where('m.is_sent = 1')
            ->andWhere('t.id = :team_id')
            ->setParameter('team_id', $team_id);

        if(!is_null($filter)){

            $filter->apply2Query($query, array
            (
                'sent_date' => 'm.sent_date:datetime_epoch',
                'owner_id'  => new DoctrineJoinFilterMapping
                (
                    'm.owner',
                    'mb',
                    "mb.id  :operator :value"
                ),
            ));
        }

        if (!is_null($order)) {

            $order->apply2Query($query, array
            (
                'sent_date' => 'm.sent_date',
                'id'        => 'm.id',
            ));
        } else {
            //default order
            $query = $query->addOrderBy("m.sent_date",'ASC');
            $query = $query->addOrderBy("m.id", 'ASC');
        }

        $query =
            $query
            ->setFirstResult($paging_info->getOffset())
            ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = array();

        foreach($paginator as $entity)
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

    /**
     * @param int $team_id
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    function getAllNotSentByTeamPaginated
    (
        $team_id,
        PagingInfo $paging_info
    )
    {
        $query  = $this->getEntityManager()->createQueryBuilder()
            ->select("m")
            ->from(\models\main\ChatTeamPushNotificationMessage::class, "m")
            ->join('m.team', 't')
            ->where('m.is_sent = 0')
            ->andWhere('t.id = :team_id')
            ->setParameter('team_id', $team_id);

        $query =
            $query
                ->setFirstResult($paging_info->getOffset())
                ->setMaxResults($paging_info->getPerPage());

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $total     = $paginator->count();
        $data      = array();

        foreach($paginator as $entity)
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