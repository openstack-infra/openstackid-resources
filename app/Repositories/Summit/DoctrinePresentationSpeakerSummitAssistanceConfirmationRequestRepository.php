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
use App\Models\Foundation\Summit\Repositories\IPresentationSpeakerSummitAssistanceConfirmationRequestRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use models\summit\PresentationSpeakerSummitAssistanceConfirmationRequest;
use models\summit\Summit;
use utils\DoctrineFilterMapping;
use Doctrine\ORM\Query\Expr\Join;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;
/**
 * Class DoctrinePresentationSpeakerSummitAssistanceConfirmationRequestRepository
 * @package App\Repositories\Summit
 */
final class DoctrinePresentationSpeakerSummitAssistanceConfirmationRequestRepository
    extends SilverStripeDoctrineRepository
    implements IPresentationSpeakerSummitAssistanceConfirmationRequestRepository
{

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
       return PresentationSpeakerSummitAssistanceConfirmationRequest::class;
    }

    protected function getFilterMappings()
    {
        return [
            'id'                => 'r.id:json_int',
            'on_site_phone'     => 'r.on_site_phone:json_string',
            'is_confirmed'      => 'r.is_confirmed',
            'registered'        => 'r.registered',
            'confirmation_date' => 'r.confirmation_date:datetime_epoch',
            'speaker'           => new DoctrineFilterMapping
            (
                "( concat(spkr.first_name, ' ', spkr.last_name) :operator ':value' ".
                "OR concat(spmm.first_name, ' ', spmm.last_name) :operator ':value' ".
                "OR spkr.first_name :operator ':value' ".
                "OR spkr.last_name :operator ':value' ".
                "OR spmm.first_name :operator ':value' ".
                "OR spmm.last_name :operator ':value' )"
            ),
            'speaker_email' => new DoctrineFilterMapping
            (
                "(sprr.email :operator ':value' OR spmm.email :operator ':value')"
            ),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderMappings()
    {
        return [
            'created'           => 'r.created',
            'confirmation_date' => 'r.confirmation_date',
            'id'                => 'r.id',
            'is_confirmed'      => 'r.is_confirmed',
            'registered'        => 'r.registered'
        ];
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
            ->select("r")
            ->from(PresentationSpeakerSummitAssistanceConfirmationRequest::class, "r")
            ->leftJoin('r.summit', 's')
            ->leftJoin('r.speaker', 'spkr')
            ->leftJoin('spkr.member', "spmm", Join::LEFT_JOIN)
            ->leftJoin('spkr.registration_request', "sprr", Join::LEFT_JOIN)
            ->where("s.id = :summit_id");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("r.id",'ASC');
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