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
use App\Models\Foundation\Summit\Repositories\ITrackTagGroupAllowedTagsRepository;
use App\Models\Foundation\Summit\TrackTagGroupAllowedTag;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\Summit;
use utils\DoctrineFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class DoctrineTrackTagGroupAllowedTagsRepository
 * @package App\Repositories\Summit
 */
final class DoctrineTrackTagGroupAllowedTagsRepository
    extends SilverStripeDoctrineRepository
    implements ITrackTagGroupAllowedTagsRepository
{
    protected function getFilterMappings()
    {
        return [
            'tag'  => new DoctrineFilterMapping
            (
                "(tg.tag :operator ':value')"
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'tag' => 'tg.tag',
            'id'  => 't.id',
        ];
    }


    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return TrackTagGroupAllowedTag::class;
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
            ->from(TrackTagGroupAllowedTag::class, "t")
            ->innerJoin('t.tag', 'tg')
            ->innerJoin('t.track_tag_group', 'g')
            ->innerJoin('g.summit', 's')
            ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("t.id",'ASC');
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