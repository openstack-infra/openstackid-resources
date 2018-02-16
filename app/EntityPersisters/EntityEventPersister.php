<?php namespace App\EntityPersisters;
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
use models\summit\SummitEntityEvent;
/**
 * Class EntityEventPersister
 * @package App\EntityPersisters
 */
final class EntityEventPersister extends BasePersister
{
    /**
     * @param SummitEntityEvent $entity_event
     */
    public static function persist(SummitEntityEvent $entity_event){
        $sql = <<<SQL
INSERT INTO SummitEntityEvent
(EntityID, EntityClassName, Type, Metadata, Created, LastEdited, OwnerID, SummitID)
VALUES (:EntityID, :EntityClassName, :Type, :Metadata, :Created, :LastEdited, :OwnerID, :SummitID)
SQL;

        $bindings = [
            'EntityID'        => $entity_event->getEntityId(),
            'EntityClassName' => $entity_event->getEntityClassName(),
            'Type'            => $entity_event->getType(),
            'Metadata'        => $entity_event->getRawMetadata(),
            'Created'         => $entity_event->getCreated(),
            'LastEdited'      => $entity_event->getLastEdited(),
            'OwnerID'         => $entity_event->getOwnerId(),
            'SummitID'        => $entity_event->getSummitId()
        ];

        $types = [
           'EntityID'        => 'integer',
           'EntityClassName' => 'string',
            'Type'           => 'string',
            'Metadata'       => 'string',
            'Created'        => 'datetime',
            'LastEdited'     => 'datetime',
            'OwnerID'        => 'integer',
            'SummitID'       => 'integer',
        ];

        self::insert($sql, $bindings, $types);
    }

    /**
     * @param SummitEntityEvent[] $entity_events
     */
    public static function persist_list(array $entity_events){
        foreach ($entity_events as $entity_event)
            self::persist($entity_event);
    }
}