<?php namespace models\summit;
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

use models\utils\IBaseRepository;
use DateTime;

/**
 * Interface ISummitEntityEventRepository
 * @package models\summit
 */
interface ISummitEntityEventRepository extends IBaseRepository
{
    /**
     * @param Summit $summit
     * @param int|null $member_id
     * @param int|null $from_id
     * @param DateTime|null $from_date
     * @param int $limit
     * @return SummitEntityEvent[]
     */
    public function getEntityEvents(Summit $summit, $member_id = null, $from_id = null, DateTime $from_date = null, $limit = 25);

    /**
     * @param Summit $summit
     * @return int
     */
    public function getLastEntityEventId(Summit $summit);
}