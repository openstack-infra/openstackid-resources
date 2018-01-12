<?php namespace App\Repositories\Summit;

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
use models\summit\ISummitAttendeeRepository;
use models\summit\Summit;
use models\summit\SummitAttendee;
use App\Repositories\SilverStripeDoctrineRepository;
use utils\DoctrineJoinFilterMapping;
use utils\DoctrineLeftJoinFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * Class DoctrineSummitAttendeeRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitAttendeeRepository
    extends SilverStripeDoctrineRepository
    implements ISummitAttendeeRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SummitAttendee::class;
    }

    /**
     * @param Summit $summit
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getBySummit(Summit $summit, PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {
        $query  = $this->getEntityManager()
            ->createQueryBuilder()
            ->select("a")
            ->from(SummitAttendee::class, "a")
            ->leftJoin('a.summit', 's')
            ->leftJoin('a.member', 'm')
            ->leftJoin('a.tickets', 't')
            ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){

            $filter->apply2Query($query, [
                'first_name'  => new DoctrineLeftJoinFilterMapping
                (
                    'a.member',
                    'm',
                    "m.first_name :operator ':value'"
                ),
                'last_name'  => new DoctrineLeftJoinFilterMapping
                (
                    'a.member',
                    'm',
                    "m.last_name :operator ':value'"
                ),
                'email'  => new DoctrineLeftJoinFilterMapping
                (
                    'a.member',
                    'm',
                    "m.email :operator ':value'"
                ),
                'external_order_id'  => new DoctrineLeftJoinFilterMapping
                (
                    'a.tickets',
                    't',
                    "t.external_order_id :operator ':value'"
                ),
                'external_attendee_id'  => new DoctrineLeftJoinFilterMapping
                (
                    'a.tickets',
                    't',
                    "t.external_attendee_id :operator ':value'"
                ),
            ]);
        }

        if (!is_null($order)) {

            $order->apply2Query($query, [
                'id'                => 'a.id',
                'first_name'        => 'm.first_name',
                'last_name'         => 'm.last_name',
                'external_order_id' => 't.external_order_id',
            ]);
        } else {
            //default order
            $query = $query->addOrderBy("m.first_name",'ASC');
            $query = $query->addOrderBy("m.last_name", 'ASC');
        }

        $query = $query
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