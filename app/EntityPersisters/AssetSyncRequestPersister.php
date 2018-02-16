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
use models\main\AssetsSyncRequest;
/**
 * Class AssetSyncRequestPersister
 * @package App\EntityPersisters
 */
final class AssetSyncRequestPersister extends BasePersister
{
    /**
     * @param AssetsSyncRequest $assets_sync_request
     */
    public static function persist(AssetsSyncRequest $assets_sync_request){
        $sql = <<<SQL
INSERT INTO AssetsSyncRequest
(
ClassName,
Created,
LastEdited,
`Origin`,
`Destination`,
Processed,
ProcessedDate
)
VALUES
('AssetsSyncRequest', NOW(), NOW(), :FromFile, :ToFile, 0, NULL );

SQL;

        $bindings = [
            'FromFile' => $assets_sync_request->getFrom(),
            'ToFile'   => $assets_sync_request->getTo(),
        ];

        $types = [
            'FromFile' => 'string',
            'ToFile'   => 'string',

        ];

        self::insert($sql, $bindings, $types);
    }
}