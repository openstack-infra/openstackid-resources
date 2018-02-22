<?php namespace App\Repositories\Summit;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Models\Foundation\Summit\Repositories\ISummitTrackRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\PresentationCategory;
use models\summit\Summit;
use utils\DoctrineFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class DoctrineSummitTrackRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitTrackRepository
    extends SilverStripeDoctrineRepository
    implements ISummitTrackRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return PresentationCategory::class;
    }

    protected function getFilterMappings()
    {
        return [
            'title'       => 't.title:json_string',
            'description' => 't.description:json_string',
            'code'        => 't.code:json_string',
            'group_name'  => new DoctrineFilterMapping
            (
                "(g.name :operator ':value')"
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'code'  => 't.code',
            'title' => 't.title',
            'id'    => 't.id',
        ];
    }

    /**
     * @param Summit $summit
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getBySummit
    (
        Summit $summit,
        PagingInfo $paging_info,
        Filter $filter = null,
        Order $order = null
    )
    {
        $query  =   $this->getEntityManager()
            ->createQueryBuilder()
            ->select("t")
            ->from(PresentationCategory::class, "t")
            ->leftJoin('t.groups', 'g')
            ->leftJoin('t.summit', 's')
            ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("t.code",'ASC');
        }

        $query = $query
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