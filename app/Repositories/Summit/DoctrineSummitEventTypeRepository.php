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
use App\Models\Foundation\Summit\Repositories\ISummitEventTypeRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\PresentationType;
use models\summit\Summit;
use models\summit\SummitEventType;
use utils\DoctrineInstanceOfFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class DoctrineSummitEventTypeRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitEventTypeRepository
    extends SilverStripeDoctrineRepository
    implements ISummitEventTypeRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SummitEventType::class;
    }

    protected function getFilterMappings()
    {
        return [
            'name'                       => 'et.type:json_string',
            'black_out_times'            => 'et.black_out_times:json_boolean',
            'use_sponsors'               => 'et.use_sponsors:json_boolean',
            'are_sponsors_mandatory'     => 'et.are_sponsors_mandatory:json_boolean',
            'allows_attachment'          => 'et.allows_attachment:json_boolean',
            'use_speakers'               => 'pt.use_speakers:json_boolean',
            'are_speakers_mandatory'     => 'pt.are_speakers_mandatory:json_boolean',
            'use_moderator'              => 'pt.use_moderator:json_boolean',
            'is_moderator_mandatory'     => 'pt.is_moderator_mandatory:json_boolean',
            'should_be_available_on_cfp' => 'pt.should_be_available_on_cfp:json_boolean',

            'class_name' => new DoctrineInstanceOfFilterMapping(
                "et",
                [
                    SummitEventType::ClassName  => SummitEventType::class,
                    PresentationType::ClassName => PresentationType::class,
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
            'name' => 'et.type',
            'id'   => 'et.id',
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
                    ->select("et")
                    ->from(SummitEventType::class, "et")
                    ->leftJoin(PresentationType::class, 'pt', 'WITH', 'pt.id = et.id')
                    ->leftJoin('et.summit', 's')
                    ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("et.type",'ASC');
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