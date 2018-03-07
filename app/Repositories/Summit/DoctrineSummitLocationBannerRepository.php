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
use App\Models\Foundation\Summit\Locations\Banners\ScheduledSummitLocationBanner;
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner;
use App\Models\Foundation\Summit\Repositories\ISummitLocationBannerRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\Summit;
use models\summit\SummitAbstractLocation;
use utils\DoctrineInstanceOfFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class DoctrineSummitLocationBannerRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitLocationBannerRepository
    extends SilverStripeDoctrineRepository
    implements ISummitLocationBannerRepository
{

    protected function getFilterMappings()
    {
        return [
            'title'      => 'b.title:json_string',
            'content'    => 'b.content:json_string',
            'type'       => 'b.type:json_string',
            'enabled'    => 'b.enabled:json_boolean',
            'start_date' => 'sb.start_date:datetime_epoch',
            'end_date'   => 'sb.end_date:datetime_epoch',
            'class_name' => new DoctrineInstanceOfFilterMapping(
                "b",
                [
                    SummitLocationBanner::ClassName          => SummitLocationBanner::class,
                    ScheduledSummitLocationBanner::ClassName => ScheduledSummitLocationBanner::class,
                ]
            )
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'id'          => 'b.id',
            'title'       => 'b.title',
            'location_id' => 'l.id',
            'start_date'  => 'sb.start_date',
            'end_date'    => 'sb.end_date'
        ];
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SummitLocationBanner::class;
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
            ->select("b")
            ->from(SummitLocationBanner::class, "b")
            ->leftJoin(ScheduledSummitLocationBanner::class, 'sb', 'WITH', 'sb.id = b.id')
            ->leftJoin('b.location', 'l')
            ->leftJoin('l.summit', 's')
            ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("b.id",'ASC');
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

    /**
     * @param SummitAbstractLocation $location
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getBySummitLocation
    (
        SummitAbstractLocation $location,
        PagingInfo $paging_info,
        Filter $filter = null,
        Order $order = null
    )
    {
        $query  =   $this->getEntityManager()
            ->createQueryBuilder()
            ->select("b")
            ->from(SummitLocationBanner::class, "b")
            ->leftJoin(ScheduledSummitLocationBanner::class, 'sb', 'WITH', 'sb.id = b.id')
            ->leftJoin('b.location', 'l')
            ->leftJoin('l.summit', 's')
            ->where("l.id = :location_id");

        $query->setParameter("location_id", $location->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("b.id",'ASC');
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