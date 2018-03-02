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

use App\Events\SummitEventDeleted;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest;
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;
use Illuminate\Support\Facades\App;

/**
 * Class SummitEventDeletedCalendarSyncWorkRequestFactory
 * @package App\Factories\CalendarAdminActionSyncWorkRequest
 */
final class SummitEventDeletedCalendarSyncWorkRequestFactory
{
    /**
     * @param SummitEventDeleted $event
     * @return AdminSummitEventActionSyncWorkRequest|null
     */
    public static function build(SummitEventDeleted $event){
        $args                    = $event->getArgs();
        $params                  = $args->getParams();
        $resource_server_context = App::make(IResourceServerContext::class);
        $member_repository       = App::make(IMemberRepository::class);
        $owner_id                = $resource_server_context->getCurrentUserExternalId();
        if($owner_id > 0){
            $member = $member_repository->getById($owner_id);
        }
        $request = null;
        if(isset($params['published']) && $params['published']){
            // just record the published state at the moment of the update

            $request = new AdminSummitEventActionSyncWorkRequest();
            $request->setSummitEventId ($params['id']);
            $request->setType(AbstractCalendarSyncWorkRequest::TypeRemove);
            if($owner_id > 0){
                $request->setCreatedBy($member);
            }

        }
        return $request;
    }
}