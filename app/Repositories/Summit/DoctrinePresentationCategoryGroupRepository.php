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
use App\Models\Foundation\Summit\Repositories\IPresentationCategoryGroupRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\PresentationCategoryGroup;
use models\summit\PrivatePresentationCategoryGroup;
use models\summit\Summit;
use utils\DoctrineFilterMapping;
use utils\DoctrineInstanceOfFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class DoctrinePresentationCategoryGroupRepository
 * @package App\Repositories\Summit
 */
final class DoctrinePresentationCategoryGroupRepository
    extends SilverStripeDoctrineRepository
    implements IPresentationCategoryGroupRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
       return PresentationCategoryGroup::class;
    }

    protected function getFilterMappings()
    {
        return [
            'name'                  => 'pcg.name:json_string',
            'description'           => 'pcg.description:json_string',
            'slug'                  => 'pcg.slug:json_string',
            'submission_begin_date' => 'ppcg.submission_begin_date:datetime_epoch',
            'submission_end_date'   => 'ppcg.submission_begin_date:datetime_epoch',
            'class_name'            => new DoctrineInstanceOfFilterMapping
            (
                "pcg",
                [
                    PresentationCategoryGroup::ClassName        => PresentationCategoryGroup::class,
                    PrivatePresentationCategoryGroup::ClassName => PrivatePresentationCategoryGroup::class,
                ]
            ),
            'track_title' => new DoctrineFilterMapping
            (
                "(cat.title :operator ':value')"
            ),
            'track_code' => new DoctrineFilterMapping
            (
                "(cat.code :operator ':value')"
            ),
            'group_title' => new DoctrineFilterMapping
            (
                "(grp.title :operator ':value')"
            ),
            'group_code' => new DoctrineFilterMapping
            (
                "(grp.code :operator ':value')"
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'name'  => 'pcg.name',
            'id'    => 'pcg.id',
            'slug'  => 'pcg.slug',
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
        Order $order   = null
    )
    {
        $query  =   $this->getEntityManager()
            ->createQueryBuilder()
            ->select("pcg")
            ->from(PresentationCategoryGroup::class, "pcg")
            ->leftJoin(PrivatePresentationCategoryGroup::class, 'ppcg', 'WITH', 'ppcg.id = pcg.id')
            ->leftJoin("pcg.categories", "cat")
            ->leftJoin("ppcg.allowed_groups", "grp")
            ->leftJoin('pcg.summit', 's')
            ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("pcg.id",'ASC');
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