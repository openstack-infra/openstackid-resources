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
use models\summit\CalendarSync\ScheduleCalendarSyncInfo;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;

/**
 * Interface ICalendarSyncRemoteFacade
 * @package services\apis\CalendarSync
 */
interface ICalendarSyncRemoteFacade
{

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @return ScheduleCalendarSyncInfo
     */
    public function addEvent(MemberEventScheduleSummitActionSyncWorkRequest $request);

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $schedule_sync_info
     * @return bool
     */
    public function deleteEvent(MemberEventScheduleSummitActionSyncWorkRequest $request, ScheduleCalendarSyncInfo $schedule_sync_info);

    /**
     * @param MemberEventScheduleSummitActionSyncWorkRequest $request
     * @param ScheduleCalendarSyncInfo $schedule_sync_info
     * @return bool
     */
    public function updateEvent(MemberEventScheduleSummitActionSyncWorkRequest $request, ScheduleCalendarSyncInfo $schedule_sync_info);

    /**
     * @param MemberCalendarScheduleSummitActionSyncWorkRequest $request
     * @param CalendarSyncInfo $calendar_sync_info
     * @return bool
     */
    public function createCalendar(MemberCalendarScheduleSummitActionSyncWorkRequest $request, CalendarSyncInfo $calendar_sync_info);

    /**
     * @param MemberCalendarScheduleSummitActionSyncWorkRequest $request
     * @param CalendarSyncInfo $calendar_sync_info
     * @return bool
     */
    public function deleteCalendar(MemberCalendarScheduleSummitActionSyncWorkRequest $request, CalendarSyncInfo $calendar_sync_info);
}