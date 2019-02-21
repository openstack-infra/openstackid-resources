<?php namespace models\summit;

/**
 * Copyright 2015 OpenStack Foundation
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

use models\main\Member;
use utils\Order;
use utils\PagingResponse;
use utils\PagingInfo;
use utils\Filter;
use models\utils\IBaseRepository;

/**
 * Interface ISpeakerRepository
 * @package models\repositories
 */
interface ISpeakerRepository extends IBaseRepository
{
    /**
     * @param Summit $summit
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getSpeakersBySummit(Summit $summit, PagingInfo $paging_info, Filter $filter = null, Order $order = null);

    /**
     * @param Member $member
     * @return PresentationSpeaker
     */
    public function getByMember(Member $member);
}