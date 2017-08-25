<?php namespace repositories\main;
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
use models\main\ChatTeamInvitation;
use models\main\IChatTeamInvitationRepository;
use App\Repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Query\Expr\Join;
/**
 * Class DoctrineChatTeamInvitationRepository
 * @package repositories\main
 */
final class DoctrineChatTeamInvitationRepository
    extends SilverStripeDoctrineRepository
    implements IChatTeamInvitationRepository
{

    /**
     * @param int $invitee_id
     * @return ChatTeamInvitation[]
     */
    function getInvitationsByInvitee($invitee_id)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select("i")
            ->from(\models\main\ChatTeamInvitation::class, "i")
            ->innerJoin('i.invitee', 'm', Join::WITH, " m.id = :member_id")
            ->setParameter('member_id', $invitee_id)->getQuery()->getResult();
    }

    /**
     * @param int $invitee_id
     * @return ChatTeamInvitation[]
     */
    function getPendingInvitationsByInvitee($invitee_id)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select("i")
            ->from(\models\main\ChatTeamInvitation::class, "i")
            ->innerJoin('i.invitee', 'm', Join::WITH, " m.id = :member_id")
            ->where('i.is_accepted = false')
            ->setParameter('member_id', $invitee_id)->getQuery()->getResult();
    }

    /**
     * @param int $invitee_id
     * @return ChatTeamInvitation[]
     */
    function getAcceptedInvitationsByInvitee($invitee_id)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select("i")
            ->from(\models\main\ChatTeamInvitation::class, "i")
            ->innerJoin('i.invitee', 'm', Join::WITH, " m.id = :member_id")
            ->where('i.is_accepted = true')
            ->setParameter('member_id', $invitee_id)->getQuery()->getResult();
    }

    /**
     * @return string
     */
    protected function getBaseEntity()
    {
        return ChatTeamInvitation::class;
    }
}