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
use models\summit\ISummitAttendeeTicketRepository;
use models\summit\SummitAttendeeTicket;
use App\Repositories\SilverStripeDoctrineRepository;
use models\utils\IEntity;

/**
 * Class DoctrineSummitAttendeeTicketRepository
 * @package App\Repositories\Summit
 */
final class DoctrineSummitAttendeeTicketRepository
    extends SilverStripeDoctrineRepository
    implements ISummitAttendeeTicketRepository
{

    /**
     * @param string $external_order_id
     * @param string $external_attendee_id
     * @return SummitAttendeeTicket
     */
    public function getByExternalOrderIdAndExternalAttendeeId($external_order_id, $external_attendee_id)
    {
        $query  = $this->getEntityManager()->createQueryBuilder()
            ->select("t")
            ->from(\models\summit\SummitAttendeeTicket::class, "t");

        $tickets = $query
            ->where('t.external_order_id = :external_order_id')
            ->andWhere('t.external_attendee_id = :external_attendee_id')
            ->setParameter('external_order_id', $external_order_id)
            ->setParameter('external_attendee_id', $external_attendee_id)->getQuery()->getResult();

        return count($tickets) > 0 ? $tickets[0] : null;
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
       return SummitAttendeeTicket::class;
    }

    /**
     * @param IEntity $entity
     * @return void
     */
    public function delete($entity)
    {
        $this->_em->getConnection()->delete("
        SummitAttendeeTicket
        ", ["ID" => $entity->getIdentifier()]);
    }
}