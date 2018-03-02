<?php namespace App\Factories\CalendarAdminActionSyncWorkRequest;
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
use App\Events\LocationAction;
use App\Models\Foundation\Summit\Repositories\ISummitLocationRepository;
use Illuminate\Support\Facades\App;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
/**
 * Class AdminSummitLocationActionSyncWorkRequestFactory
 * @package App\Factories\CalendarAdminActionSyncWorkRequest
 */
final class AdminSummitLocationActionSyncWorkRequestFactory
{
    /**
     * @param LocationAction $event
     * @return AdminSummitLocationActionSyncWorkRequest
     */
    public static function build(LocationAction $event, $type){
        $resource_server_context         = App::make(IResourceServerContext::class);
        $member_repository               = App::make(IMemberRepository::class);
        $location_repository             = App::make(ISummitLocationRepository::class);
        $owner_id                        = $resource_server_context->getCurrentUserExternalId();
        if(is_null($owner_id)) $owner_id = 0;

        $request = new AdminSummitLocationActionSyncWorkRequest();
        $location    = $location_repository->getById($event->getLocationId());
        $request->setLocationId($location);

        $request->Type = $type;
        if($owner_id > 0){
            $member = $member_repository->getById($owner_id);
            $request->setCreatedBy($member);
        }

        return $request;
    }
}