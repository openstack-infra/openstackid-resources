<?php namespace App\Factories\EntityEvents;
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
use App\Events\SummitVenueRoomUpdated;
use Illuminate\Support\Facades\App;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use models\summit\SummitEntityEvent;
/**
 * Class LocationActionEntityEventFactory
 * @package App\Factories\EntityEvents
 */
final class LocationActionEntityEventFactory
{
    /**
     * @param LocationAction $event
     * @param string $type
     * @return SummitEntityEvent
     */
    public static function build(LocationAction $event, $type = 'UPDATE')
    {
        $resource_server_context = App::make(IResourceServerContext::class);
        $member_repository       = App::make(IMemberRepository::class);
        $summit_repository       = App::make(ISummitRepository::class);
        $summit                  = $summit_repository->getById($event->getSummitId());
        $owner_id                = $resource_server_context->getCurrentUserExternalId();

        if (is_null($owner_id)) $owner_id = 0;

        $entity_event = new SummitEntityEvent;
        $entity_event->setEntityClassName($event->getLocationClassName());
        $entity_event->setEntityId($event->getLocationId());
        $entity_event->setType($type);

        if ($owner_id > 0) {
            $member = $member_repository->getById($owner_id);
            $entity_event->setOwner($member);
        }

        $metadata = '';

        if($event instanceof SummitVenueRoomUpdated){
            $old_floor_id = $event->getOldFloorId();
            $new_floor_id = $event->getNewFloorId();

            if($old_floor_id != $new_floor_id){
                $metadata = json_encode( ['floor_old' => $old_floor_id, 'floor_new' => $new_floor_id]);
            }
        }

        $entity_event->setSummit($summit);
        $entity_event->setMetadata($metadata);

        return $entity_event;
    }
}