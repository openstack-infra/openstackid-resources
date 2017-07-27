<?php namespace services\model;
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
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberCalendarScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberEventScheduleSummitActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\MemberScheduleSummitActionSyncWorkRequest;

/**
 * Class MemberScheduleWorkQueueManager
 * @package services\model
 */
final class MemberScheduleWorkQueueManager
{
    /**
     * @var array
     */
    private $registered_requests = [];
    /**
     * @var array
     */
    private $calendars_events    = [];

    /**
     * @param MemberScheduleSummitActionSyncWorkRequest $request
     * @return string
     */
    private function getKey(MemberScheduleSummitActionSyncWorkRequest $request){
        $event_id = null;
        if($request instanceof MemberEventScheduleSummitActionSyncWorkRequest){
            $event_id = $request->getSummitEvent()->getId();
        }
        return $this->generateKey($request->getType(), $request->getCalendarSyncInfo()->getIdentifier(), $event_id);
    }

    /**
     * @param string $type
     * @param int $calendar_id
     * @param null|int $event_id
     * @return string
     */
    private function generateKey($type, $calendar_id, $event_id = null){
        $sub_type = is_null($event_id) ? MemberCalendarScheduleSummitActionSyncWorkRequest::SubType : MemberEventScheduleSummitActionSyncWorkRequest::SubType;
        $key      = "{$sub_type}_{$type}_{$calendar_id}";
        if(!is_null($event_id)){
            $key .= "_{$event_id}";
        }
        return $key;
    }

    /**
     * @param MemberScheduleSummitActionSyncWorkRequest $request
     * @return bool
     */
    public function registerRequest(MemberScheduleSummitActionSyncWorkRequest $request){
        $key = $this->getKey($request);
        if(isset($this->registered_requests[$key])) return false;
        $this->registered_requests[$key] = $request;
        // register request per member calendar
        if($request instanceof MemberEventScheduleSummitActionSyncWorkRequest) {
            $calendar_info_id = $request->getCalendarSyncInfo()->getIdentifier();
            if (!isset($this->calendars_events[$calendar_info_id]))
                $this->calendars_events[$calendar_info_id] = [];
            $this->calendars_events[$calendar_info_id][] = $request;
        }

        return true;
    }

    /**
     * @param int $calendar_info_id
     * @return MemberEventScheduleSummitActionSyncWorkRequest[]
     */
    public function getPendingEventsForCalendar($calendar_info_id){
        if (isset($this->calendars_events[$calendar_info_id])){
            return $this->calendars_events[$calendar_info_id];
        }
        return [];
    }

    /**
     * @param int $calendar_info_id
     * @return bool
     */
    public function clearPendingEventsForCalendar($calendar_info_id){
        if (isset($this->calendars_events[$calendar_info_id])){
            unset($this->calendars_events[$calendar_info_id]);
            return true;
        }
        return false;
    }

    /**
     * @param int $calendar_id
     * @param int $event_id
     * @param string $type
     * @return MemberEventScheduleSummitActionSyncWorkRequest[]
     */
    public function getSummitEventRequestFor($calendar_id, $event_id, $type = null){
        $types = [
            AbstractCalendarSyncWorkRequest::TypeAdd,
            AbstractCalendarSyncWorkRequest::TypeRemove,
            AbstractCalendarSyncWorkRequest::TypeUpdate,
        ];

        if(!empty($type)) $types = [$type];
        $list = [];
        foreach ($types as $t){
            $key = $this->generateKey($t, $calendar_id, $event_id);
            if(isset($this->registered_requests[$key])){
                $list[] = $this->registered_requests[$key];
            }
        }
        return $list;
    }

    /**
     * @param int $calendar_id
     * @param string $type
     * @return MemberCalendarScheduleSummitActionSyncWorkRequest|null
     */
    public function getCalendarRequestFor($calendar_id, $type){
        $key = $this->generateKey($type, $calendar_id);
        return isset($this->registered_requests[$key]) ? $this->registered_requests[$key] : null;
    }

    /**
     * @param MemberScheduleSummitActionSyncWorkRequest $request
     * @return bool
     */
    public function removeRequest(MemberScheduleSummitActionSyncWorkRequest $request){
        $key = $this->getKey($request);
        if(isset($this->registered_requests[$key])){
            unset($this->registered_requests[$key]);
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getPurgedRequests(){
        return array_values($this->registered_requests);
    }
}