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
use App\Models\Foundation\Summit\Repositories\ISummitLocationRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use models\summit\Summit;
use models\summit\SummitAbstractLocation;
use models\summit\SummitAirport;
use models\summit\SummitExternalLocation;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitHotel;
use models\summit\SummitVenue;
use utils\DoctrineInstanceOfFilterMapping;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class DoctrineSummitLocationRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitLocationRepository
    extends SilverStripeDoctrineRepository
    implements ISummitLocationRepository
{

    private static $forbidded_classes = [
        'models\\summit\\SummitVenueRoom',
    ];

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return SummitAbstractLocation::class;
    }

    protected function getFilterMappings()
    {
        return [
            'name'        => 'al.name:json_string',
            'description' => 'al.description:json_string',
            'address1'    => 'gll.address1:json_string',
            'address2'    => 'gll.address2:json_string',
            'zip_code'    => 'gll.zip_code:json_string',
            'city'        => 'gll.city:json_string',
            'state'       => 'gll.state:json_string',
            'country'     => 'gll.country:json_string',
            'sold_out'    => 'h.sold_out:json_boolean',
            'is_main'     => 'v.is_main:json_boolean',
            'class_name'  => new DoctrineInstanceOfFilterMapping(
                "al",
                [
                    SummitVenue::ClassName            => SummitVenue::class,
                    SummitHotel::ClassName            => SummitHotel::class,
                    SummitExternalLocation::ClassName => SummitExternalLocation::class,
                    SummitAirport::ClassName          => SummitAirport::class,
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
            'id'    => 'al.id',
            'name'  => 'al.name',
            'order' => 'al.order',
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
            ->select("al")
            ->from(SummitAbstractLocation::class, "al")
            ->leftJoin(SummitGeoLocatedLocation::class, 'gll', 'WITH', 'gll.id = al.id')
            ->leftJoin(SummitVenue::class, 'v', 'WITH', 'v.id = gll.id')
            ->leftJoin(SummitExternalLocation::class, 'el', 'WITH', 'el.id = gll.id')
            ->leftJoin(SummitHotel::class, 'h', 'WITH', 'h.id = el.id')
            ->leftJoin(SummitAirport::class, 'ap', 'WITH', 'ap.id = el.id')
            ->leftJoin('al.summit', 's')
            ->where("s.id = :summit_id")
            ->andWhere("not al INSTANCE OF ('" . implode("','", self::$forbidded_classes) . "')");

        $query->setParameter("summit_id", $summit->getId());

        if(!is_null($filter)){
            $filter->apply2Query($query, $this->getFilterMappings());
        }

        if (!is_null($order)) {
            $order->apply2Query($query, $this->getOrderMappings());
        } else {
            //default order
            $query = $query->addOrderBy("al.id",'ASC');
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
     * @param Summit $summit
     * @return array
     */
    public function getMetadata(Summit $summit)
    {
        return [
            SummitVenue::getMetadata(),
            SummitAirport::getMetadata(),
            SummitHotel::getMetadata(),
            SummitExternalLocation::getMetadata()
        ];
    }
}