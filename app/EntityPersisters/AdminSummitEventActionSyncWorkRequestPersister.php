<?php namespace App\EntityPersisters;
use models\summit\CalendarSync\WorkQueue\AdminSummitEventActionSyncWorkRequest;

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

class AdminSummitEventActionSyncWorkRequestPersister extends BasePersister
{
    public static function persist(AdminSummitEventActionSyncWorkRequest $request){

        $sql = <<<SQL
INSERT INTO AbstractCalendarSyncWorkRequest 
(`Type`, IsProcessed, ProcessedDate, Created, LastEdited, ClassName)
VALUES (:Type, 0, NULL, NOW(), NOW(), 'AdminSummitEventActionSyncWorkRequest');
INSERT INTO AdminScheduleSummitActionSyncWorkRequest (ID, CreatedByID) VALUES (LAST_INSERT_ID(), :CreatedByID)
INSERT INTO AdminSummitEventActionSyncWorkRequest (ID, SummitEventID) VALUES (LAST_INSERT_ID(), :SummitEventID);
SQL;

        $bindings = [
            'Type'          => $request->getType(),
            'CreatedByID'   => $request->getCreatedById(),
            'SummitEventID' => $request->getSummitEventId(),
        ];

        $types = [
            'Type'          => 'string',
            'CreatedByID'   => 'integer',
            'SummitEventID' => 'integer',
        ];

        self::insert($sql, $bindings, $types);

    }
}