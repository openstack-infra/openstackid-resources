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

use models\summit\ISummitRepository;
use models\summit\Summit;
use repositories\SilverStripeDoctrineRepository;

/**
 * Class DoctrineSummitRepository
 * @package repositories\summit
 */
final class DoctrineSummitRepository extends SilverStripeDoctrineRepository implements ISummitRepository
{


    /**
     * @return Summit
     */
    public function getCurrent()
    {
        $res = $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from(\models\summit\Summit::class, "s")
            ->where('s.active = 1')
            ->andWhere('s.available_on_api = 1')
            ->orderBy('s.begin_date', 'DESC')
            ->getQuery()
            ->getResult();
        if (count($res) == 0) return null;
        return $res[0];
    }

    /**
     * @return Summit[]
     */
    public function getAvailables()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select("s")
            ->from(\models\summit\Summit::class, "s")
            ->where('s.available_on_api = 1')
            ->orderBy('s.begin_date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}