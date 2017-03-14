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
use models\summit\SummitEvent;
/**
 * Class SummitEventEntityEventType
 * @package Models\foundation\summit\EntityEvents
 */
abstract class SummitEventEntityEventType extends EntityEventType
{
    /**
     * @return IEntity|null
     */
    protected function registerEntity(){
        $entity = $this->getEntity();
        if(!is_null($entity))
            $this->entity_event->registerEntity($entity);
        return $entity;
    }

    /**
     * @return SummitEvent|null
     */
    protected function getEntity(){
        $this->evictEntity();
        return $this->entity_event->getSummit()->getScheduleEvent($this->entity_event->getEntityId());
    }
}