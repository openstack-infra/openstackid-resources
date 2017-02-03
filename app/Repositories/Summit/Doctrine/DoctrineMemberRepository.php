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
use models\main\IMemberRepository;
use models\main\Member;
use repositories\SilverStripeDoctrineRepository;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class DoctrineMemberRepository
 * @package repositories\summit
 */
final class DoctrineMemberRepository extends SilverStripeDoctrineRepository implements IMemberRepository
{

    /**
     * @param string $email
     * @return Member
     */
    public function getByEmail($email)
    {
        // TODO: Implement getByEmail() method.
    }

    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getAllByPage(PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {
        $query  = $this->getEntityManager()
                ->createQueryBuilder()
                ->select("m")
                ->from(\models\main\Member::class, "m")
                ->where("m.active = 1")
                ->andWhere("m.first_name is not null")
                ->andWhere("m.last_name is not null")
                ->andWhere("m.email_verified = 1");

        if(!is_null($filter)){

            $filter->apply2Query($query, [
                'irc'        => 'm.irc_handle:json_string',
                'twitter'    => 'm.twitter_handle:json_string',
                'first_name' => 'm.first_name:json_string',
                'last_name'  => 'm.last_name:json_string',
                'email'      => ['m.email:json_string', 'm.second_email:json_string', 'm.third_email:json_string'],
            ]);
        }

        if (!is_null($order)) {

            $order->apply2Query($query, array
            (
                'id'          => 'm.id',
                'first_name'  => 'm.first_name',
                'last_name'   => 'm.last_name',
            ));
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