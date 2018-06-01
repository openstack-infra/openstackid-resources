<?php namespace App\Services\Apis\CalendarSync;
/**
 * Copyright 2018 OpenStack Foundation
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
use services\apis\CalendarSync\ICalendarSyncRemoteFacade;
/**
 * Interface ICalendarSyncRemoteFacadeFactory
 * @package App\Services\Apis\CalendarSync
 */
interface ICalendarSyncRemoteFacadeFactory
{
    /**
     * @param CalendarSyncInfo $sync_calendar_info
     * @return ICalendarSyncRemoteFacade|null
     */
    public function build(CalendarSyncInfo $sync_calendar_info);
}