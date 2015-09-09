<?php
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

namespace repositories\summit;

use models\summit\ISummitEventRepository;
use models\summit\Summit;
use models\summit\SummitEvent;
use models\utils\EloquentBaseRepository;

/**
 * Class EloquentSummitEventRepository
 * @package repositories\summit
 */
final class EloquentSummitEventRepository extends EloquentBaseRepository implements ISummitEventRepository
{
    /**
     * @param SummitEvent $event
     */
    public function __construct(SummitEvent $event)
    {
        $this->entity = $event;
    }

    /**
     * @param SummitEvent $event
     * @return SummitEvent[]
     */
    public function getPublishedOnSameTimeFrame(SummitEvent $event)
    {
        $summit     = $event->getSummit();
        $end_date   = $event->EndDateUTC;
        $start_date = $event->StartDateUTC;
        return $this->entity
            ->where('SummitID', '=', $summit->getIdentifier())
            ->where('Published', '=', 1)
            ->where('StartDate', '<=', $end_date->format('Y-m-d H:i:s'))
            ->where('EndDate',   '>=', $start_date->format('Y-m-d H:i:s'))
            ->get();
    }
}