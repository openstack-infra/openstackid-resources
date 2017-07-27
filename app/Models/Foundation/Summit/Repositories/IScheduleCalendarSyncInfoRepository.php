<?php namespace models\summit;
/**
 * Copyright 2017 OpenStack Foundation
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
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Interface IScheduleCalendarSyncInfoRepository
 * @package models\summit
 */
interface IScheduleCalendarSyncInfoRepository extends IBaseRepository
{
    /**
     * @param SummitEvent $summit_event
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getAllBySummitEvent(SummitEvent $summit_event, PagingInfo $paging_info);

    /**
     * @param SummitAbstractLocation $location
     * @param PagingInfo $paging_info
     * @return PagingResponse
     */
    public function getAllBySummitLocation(SummitAbstractLocation $location, PagingInfo $paging_info);

}