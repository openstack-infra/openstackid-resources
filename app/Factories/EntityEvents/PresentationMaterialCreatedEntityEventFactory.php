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
use App\Events\PresentationMaterialCreated;
use Illuminate\Support\Facades\App;
use models\summit\SummitEntityEvent;
/**
 * Class PresentationMaterialCreatedEntityEventFactory
 * @package App\Factories\EntityEvents
 */
final class PresentationMaterialCreatedEntityEventFactory
{
    /**
     * @param PresentationMaterialCreated $event
     * @return SummitEntityEvent
     */
    public static function build(PresentationMaterialCreated $event){
        $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
        $member_repository               = App::make(\models\main\IMemberRepository::class);
        $owner_id                        = $resource_server_context->getCurrentUserExternalId();
        if(is_null($owner_id)) $owner_id = 0;

        $entity_event                  = new SummitEntityEvent();
        $entity_event->setEntityClassName($event->getMaterial()->getClassName());
        $entity_event->setEntityId($event->getMaterial()->getId());
        $entity_event->setType('INSERT');

        if($owner_id > 0){
            $member = $member_repository->getById($owner_id);
            $entity_event->setOwner($member);
        }

        $entity_event->setSummit($event->getMaterial()->getPresentation()->getSummit());
        $entity_event->setMetadata(json_encode([ 'presentation_id' => intval($event->getMaterial()->getPresentation()->getId())]));

        return $entity_event;
    }
}