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
 * Class SummitLocationImageEventType
 * @package Models\foundation\summit\EntityEvents
 */
final class SummitLocationImageEventType extends GenericSummitEntityEventType
{

    /**
     * @return IEntity|null
     */
    protected function registerEntity()
    {
        $metadata = $this->entity_event->getMetadata();
        if(!isset($metadata['location_id'])) return null;
        $location   = $this->entity_event->getSummit()->getLocation(intval($metadata['location_id']));
        if(is_null($location)) return null;
        $this->evictEntity();
        $entity = $location->getImage($this->entity_event->getEntityId());
        if(is_null($entity)) return null;
        $this->entity_event->registerEntity($entity);
        return $entity;
    }
}