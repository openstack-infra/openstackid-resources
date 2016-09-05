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

use models\summit\SummitEntityEvent;
use models\utils\IEntity;

/**
 * Class EntityEventType
 * @package Models\foundation\summit\EntityEvents
 */
abstract class EntityEventType implements IEntityEventType
{
    /**
     * @var SummitEntityEvent
     */
    protected $entity_event;

    /**
     * @var SummitEntityEventProcessContext
     */
    protected $process_ctx;

    /**
     * SummitEntityEventType constructor.
     * @param SummitEntityEvent $entity_event
     * @param SummitEntityEventProcessContext $process_ctx
     */
    public function __construct(SummitEntityEvent $entity_event, SummitEntityEventProcessContext $process_ctx)
    {
        $this->entity_event = $entity_event;
        $this->process_ctx  = $process_ctx;
    }

    /**
     * @return IEntity|null
     */
    abstract protected function registerEntity();
}