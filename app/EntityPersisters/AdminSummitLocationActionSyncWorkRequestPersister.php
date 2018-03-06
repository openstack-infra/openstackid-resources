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
use models\summit\CalendarSync\WorkQueue\AdminSummitLocationActionSyncWorkRequest;
/**
 * Class AdminSummitLocationActionSyncWorkRequestPersister
 * @package App\EntityPersisters
 */
final class AdminSummitLocationActionSyncWorkRequestPersister extends BasePersister
{
    /**
     * @param AdminSummitLocationActionSyncWorkRequest $request
     */
    public static function persist(AdminSummitLocationActionSyncWorkRequest $request){

        $sql = <<<SQL
INSERT INTO AbstractCalendarSyncWorkRequest 
(`Type`, IsProcessed, ProcessedDate, Created, LastEdited, ClassName)
VALUES (:Type, 0, NULL, NOW(), NOW(), 'AdminSummitLocationActionSyncWorkRequest');
SQL;

        $bindings = [
            'Type'          => $request->getType(),
            'CreatedByID'   => $request->getCreatedById(),
            'LocationID'    => $request->getLocationId(),
        ];

        $types = [
            'Type'          => 'string',
            'CreatedByID'   => 'integer',
            'LocationID'    => 'integer',
        ];

        self::insert($sql, $bindings, $types);

        $sql = <<<SQL
INSERT INTO AdminScheduleSummitActionSyncWorkRequest (ID, CreatedByID) VALUES (LAST_INSERT_ID(), :CreatedByID)
SQL;
        self::insert($sql, $bindings, $types);

        $sql = <<<SQL
INSERT INTO AdminSummitLocationActionSyncWorkRequest (ID, LocationID) VALUES (LAST_INSERT_ID(), :LocationID);
SQL;

        self::insert($sql, $bindings, $types);

    }
}