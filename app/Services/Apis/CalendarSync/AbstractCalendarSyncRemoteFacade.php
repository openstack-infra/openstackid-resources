<?php namespace services\apis\CalendarSync;
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

use models\summit\CalendarSync\CalendarSyncInfo;

/**
 * Class AbstractCalendarSyncRemoteFacade
 * @package services\apis\CalendarSync
 */
abstract class AbstractCalendarSyncRemoteFacade
    implements ICalendarSyncRemoteFacade
{
    /**
     * @var CalendarSyncInfo
     */
    protected $sync_calendar_info;

    /**
     * AbstractCalendarSyncRemoteFacade constructor.
     * @param CalendarSyncInfo $sync_calendar_info
     */
    public function __construct(CalendarSyncInfo $sync_calendar_info)
    {
        $this->sync_calendar_info = $sync_calendar_info;
    }

    /**
     * @return mixed
     */
    abstract public function getSleepInterval();
}