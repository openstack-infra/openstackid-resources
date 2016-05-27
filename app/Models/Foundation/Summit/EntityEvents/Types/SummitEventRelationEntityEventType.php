<?php namespace Models\foundation\summit\EntityEvents;

/**
 * Copyright 2016 OpenStack Foundation
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

use models\utils\IEntity;

/**
 * Class SummitEventRelationEntityEventType
 * @package Models\foundation\summit\EntityEvents
 */
final class SummitEventRelationEntityEventType extends EntityEventType
{

    /**
     * @return IEntity|null
     */
    protected function registerEntity()
    {
        $metadata = $this->entity_event->getMetadata();
        if(!isset($metadata['event_id'])) return null;
        $event = $this->entity_event->getSummit()->getScheduleEvent(intval($metadata['event_id']));
        if (is_null($event)) return null;
        $this->entity_event->registerEntity($event);
        return $event;
    }

    /**
     * @return void
     */
    public function process()
    {
        $entity = $this->registerEntity();
        if(is_null($entity)) return;
        $this->entity_event->setType('UPDATE');
        if($this->process_ctx->existsUpdateOp($this->entity_event)) return;
        $this->process_ctx->registerUpdateOp($this->entity_event);
        $this->process_ctx->registerEntityEvent($this->entity_event);
    }
}