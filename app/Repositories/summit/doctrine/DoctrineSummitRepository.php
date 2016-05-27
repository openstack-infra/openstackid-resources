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

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Join;
use models\summit\ISummitRepository;
use models\summit\Summit;
use repositories\SilverStripeDoctrineRepository;
use utils\DoctrineJoinFilterMapping;
use utils\Filter;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class DoctrineSummitRepository
 * @package repositories\summit
 */
final class DoctrineSummitRepository extends SilverStripeDoctrineRepository implements ISummitRepository
{

    /**
     * @param int $summit_id
     * @param PagingInfo $paging_info
     * @param bool|false $published
     * @param Filter|null $filter
     * @return PagingResponse
     */
    public function getEvents($summit_id, PagingInfo $paging_info, $published = false, Filter $filter = null)
    {
        $query  = $this->getEntityManager()->createQueryBuilder()
            ->select("e")
            ->from(\models\summit\SummitEvent::class, "e")
            ->join('e.summit', 's', Join::WITH, " s.id = :summit_id")
            ->setParameter('summit_id', $summit_id);

        if($published){
           $query = $query->where('e.published = 1');
        }

        if(!is_null($filter)){

            $filter->apply2Query($query, array
            (
                'title'         => 'e.title',
                'start_date'    => 'e.start_date:datetime_epoch',
                'end_date'      => 'e.end_date:datetime_epoch',
                'tags'          => new DoctrineJoinFilterMapping
                (
                    'e.tags',
                    't',
                    "t.tag :operator ':value'"
                ),
                'summit_type_id'=> new DoctrineJoinFilterMapping
                (
                    'e.tags',
                    't',
                    "t.tag :operator ':value'"
                ),
                'event_type_id' => new DoctrineJoinFilterMapping
                (
                    'e.tags',
                    't',
                    "t.tag :operator ':value'"
                ),
            ));
        }

        $query= $query
            ->addOrderBy("e.start_date")
            ->addOrderBy("e.end_date")
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
     * @return Summit
     */
    public function getCurrent()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from(\models\summit\Summit::class, "s")
            ->where('s.active = 1')
            ->orderBy('s.begin_date','DESC')
            ->getQuery()->getOneOrNullResult();
    }
}