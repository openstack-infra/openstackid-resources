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
 * Class WipeDataEntityEventType
 * @package Models\foundation\summit\EntityEvents
 */
final class WipeDataEntityEventType extends EntityEventType
{

    /**
     * @return IEntity|null
     */
    protected function registerEntity()
    {
        return null;
    }

    /**
     * @return void
     */
    public function process()
    {
        // if event is for a particular user
        if ($this->entity_event->getEntityId() > 0) {
            // if we are not the recipient or its already processed then continue
            if ($this->process_ctx->getCurrentMemberId() !== intval($this->entity_event->getEntityId()))
                return;
        }
        $this->entity_event->setType('TRUNCATE');
        $this->process_ctx->registerEntityEvent($this->entity_event);
    }
}