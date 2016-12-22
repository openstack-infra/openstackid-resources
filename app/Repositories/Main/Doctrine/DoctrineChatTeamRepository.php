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
use models\main\ChatTeam;
use models\main\IChatTeamRepository;
use models\main\Member;
use repositories\SilverStripeDoctrineRepository;
use Doctrine\ORM\Query\Expr\Join;
/**
 * Class DoctrineChatTeamRepository
 * @package repositories\main
 */
final class DoctrineChatTeamRepository extends SilverStripeDoctrineRepository implements IChatTeamRepository
{

    /**
     * @param Member $member
     * @return ChatTeam[]
     */
    function getTeamsByMember(Member $member)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select("t")
            ->from(\models\main\ChatTeam::class, "t")
            ->innerJoin('t.members', 'tm')
            ->innerJoin('tm.member', 'm', Join::WITH, "m.id = :member_id")
            ->setParameter('member_id', $member->getId())->getQuery()->getResult();
    }

    /**
     * @return int[]
     */
    function getAllTeamsIdsWithPendingMessages2Sent()
    {
        $result = $this
            ->getEntityManager()
            ->createQuery("select t.id from \models\main\ChatTeam t join t.messages m where exists (select m2.id from \models\main\ChatTeamPushNotificationMessage m2 where m2.id = m.id and m2.is_sent = 0 )")
            ->getScalarResult();
        $ids = array_map('current', $result);
        return $ids;
    }
}