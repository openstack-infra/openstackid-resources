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
use App\Events\SummitEventUpdated;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Illuminate\Support\Facades\App;
use models\summit\SummitEntityEvent;
/**
 * Class SummitEventUpdatedEntityEventFactory
 * @package App\Factories\EntityEvents
 */
final class SummitEventUpdatedEntityEventFactory
{
    /**
     * @param SummitEventUpdated $event
     * @return SummitEntityEvent|void
     */
    public static function build(SummitEventUpdated $event){
        $args = $event->getArgs();
        if(!$args instanceof PreUpdateEventArgs) return;

        $resource_server_context         = App::make(\models\oauth2\IResourceServerContext::class);
        $member_repository               = App::make(\models\main\IMemberRepository::class);

        $owner_id                        = $resource_server_context->getCurrentUserExternalId();
        if(is_null($owner_id)) $owner_id = 0;

        $entity_event                  = new SummitEntityEvent();
        $entity_event->setEntityClassName($event->getSummitEvent()->getClassName());
        $entity_event->setEntityId($event->getSummitEvent()->getId());
        $entity_event->setType('UPDATE');

        if($owner_id > 0){
            $member = $member_repository->getById($owner_id);
            $entity_event->setOwner($member);
        }

        $entity_event->setSummit($event->getSummitEvent()->getSummit());

        // check if there was a change on publishing state
        if($args->hasChangedField('published')){
            $pub_old  = intval($args->getOldValue('published'));
            $pub_new  = intval($args->getNewValue('published'));
            $metadata = json_encode([ 'pub_old'=> $pub_old,  'pub_new' => $pub_new]);
        }
        else
            $metadata = json_encode([ 'pub_new' => intval($event->getSummitEvent()->getPublished())]);

        $entity_event->setMetadata($metadata);
        return $entity_event;
    }
}