<?php namespace App\Services\Model;
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
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;

/**
 * Class AdminScheduleWorkQueueManager
 * @package App\Services\Model
 */
final class AdminScheduleWorkQueueManager
implements ICalendarSyncWorkRequestQueueManager
{
    /**
     * @var array
     */
    private $registered_requests = [];

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return string
     */
    private function getKey(AbstractCalendarSyncWorkRequest $request){
        $event_id    = null;
        $location_id = null;
        if($request instanceof AdminSummitEventActionSyncWorkRequest){
            $event_id = $request->getSummitEventId();
        }
        if($request instanceof AdminSummitLocationActionSyncWorkRequest){
            $location_id = $request->getLocation()->getId();
        }
        return $this->generateKey($request->getType(), $event_id, $location_id);
    }

    /**
     * @param string $type
     * @param int $event_id
     * @param int $location_id
     * @return string
     */
    private function generateKey($type, $event_id = null, $location_id = null){
        $sub_type = !is_null($event_id) ? AdminSummitEventActionSyncWorkRequest::SubType : AdminSummitLocationActionSyncWorkRequest::SubType;
        $id       = !is_null($event_id) ? $event_id : $location_id;
        $key      = "{$sub_type}_{$type}_{$id}";
        return $key;
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return bool
     */
    public function registerRequest(AbstractCalendarSyncWorkRequest $request){
        $key = $this->getKey($request);
        if(isset($this->registered_requests[$key])) return false;
        $this->registered_requests[$key] = $request;
        return true;
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return bool
     */
    public function removeRequest(AbstractCalendarSyncWorkRequest $request){
        $key = $this->getKey($request);
        if(isset($this->registered_requests[$key])){
            unset($this->registered_requests[$key]);
            return true;
        }
        return false;
    }

    /**
     * @param int $event_id
     * @param string $type
     * @return AdminSummitEventActionSyncWorkRequest[]
     */
    public function getSummitEventRequestFor($event_id, $type = null){
        $types = [
            AbstractCalendarSyncWorkRequest::TypeAdd,
            AbstractCalendarSyncWorkRequest::TypeRemove,
            AbstractCalendarSyncWorkRequest::TypeUpdate,
        ];

        if(!empty($type)) $types = [$type];
        $list = [];
        foreach ($types as $t){
            $key = $this->generateKey($t, $event_id);
            if(isset($this->registered_requests[$key])){
                $list[] = $this->registered_requests[$key];
            }
        }
        return $list;
    }

    /**
     * @param int $location_id
     * @param string $type
     * @return AdminSummitLocationActionSyncWorkRequest[]
     */
    public function getSummitLocationRequestFor($location_id, $type = null){
        $types = [
            AbstractCalendarSyncWorkRequest::TypeAdd,
            AbstractCalendarSyncWorkRequest::TypeRemove,
            AbstractCalendarSyncWorkRequest::TypeUpdate,
        ];

        if(!empty($type)) $types = [$type];
        $list = [];
        foreach ($types as $t){
            $key = $this->generateKey($t, null, $location_id);
            if(isset($this->registered_requests[$key])){
                $list[] = $this->registered_requests[$key];
            }
        }
        return $list;
    }
    /**
     * @return array
     */
    public function getPurgedRequests(){
        return array_values($this->registered_requests);
    }

    /**
     * @param AbstractCalendarSyncWorkRequest $request
     * @return bool
     */
    public function registerRequestForDelete(AbstractCalendarSyncWorkRequest $request)
    {
        // TODO: Implement registerRequestForDelete() method.
    }

    /**
     * @return array
     */
    public function getRequestsToDelete()
    {
        // TODO: Implement getRequestsToDelete() method.
    }
}