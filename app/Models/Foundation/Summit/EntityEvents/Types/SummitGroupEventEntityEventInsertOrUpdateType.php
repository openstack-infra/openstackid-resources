<?php namespace Models\foundation\summit\EntityEvents;

/**
 * Copyright 2017 OpenStack Foundation
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
use models\summit\Summit;
use models\summit\SummitGroupEvent;

/**
 * Class SummitGroupEventEntityEventInsertOrUpdateType
 * @package Models\foundation\summit\EntityEvents
 */
final class SummitGroupEventEntityEventInsertOrUpdateType extends SummitEventEntityEventType
{

    /**
     * @return void
     */
    public function process()
    {
        $metadata          = $this->entity_event->getMetadata();
        $published_old     = isset($metadata['pub_old']) ? (bool)intval($metadata['pub_old']) : false;
        $published_current = isset($metadata['pub_new']) ? (bool)intval($metadata['pub_new']) : false;

        // the event was not published at the moment of UPDATE .. then skip it!
        if (!$published_old && !$published_current) return;
        $entity = $this->getEntity();

        if (!$entity instanceof SummitGroupEvent) return;

        $current_member = $this->process_ctx->getCurrentMember();

        if (is_null($current_member)) return;

        if (!Summit::allowToSee($entity, $current_member)) return;

        if (!is_null($entity)) // if event exists its bc its published
        {
            $this->registerEntity();
            $this->entity_event->setType
            (
                $published_current && isset($metadata['pub_old']) && !$published_old ?
                    'INSERT' :
                    $this->entity_event->getType()
            );

            $this->process_ctx->registerEntityEvent($this->entity_event);
            $this->process_ctx->registerSummitEventOp($this->entity_event);
            return;
        }
        // if does not exists on schedule delete it
        $this->entity_event->setType('DELETE');
        $chain = new SummitEventEntityEventDeleteType($this->entity_event, $this->process_ctx);
        return $chain->process();
    }
}