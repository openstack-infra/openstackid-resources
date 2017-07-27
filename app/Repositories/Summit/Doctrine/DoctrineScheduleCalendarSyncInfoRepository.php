<?php namespace repositories\summit;

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

use models\summit\IScheduleCalendarSyncInfoRepository;
use models\summit\SummitAbstractLocation;
use models\summit\SummitEvent;
use repositories\SilverStripeDoctrineRepository;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class DoctrineScheduleCalendarSyncInfoRepository
 * @package repositories\summit
 */
final class DoctrineScheduleCalendarSyncInfoRepository extends SilverStripeDoctrineRepository
    implements IScheduleCalendarSyncInfoRepository
{

    /**
     * @param SummitEvent $event
     * @param PagingInfo $page_info
     * @return PagingResponse
     */
    public function getAllBySummitEvent(SummitEvent $event, PagingInfo $page_info)
    {
        // TODO: Implement getAllBySummitEvent() method.
    }

    /**
     * @param SummitAbstractLocation $location
     * @param PagingInfo $page_info
     * @return PagingResponse
     */
    public function getAllBySummitLocation(SummitAbstractLocation $location, PagingInfo $page_info)
    {
        // TODO: Implement getAllBySummitLocation() method.
    }

}