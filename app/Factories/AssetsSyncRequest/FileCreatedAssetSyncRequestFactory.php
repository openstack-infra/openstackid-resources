<?php namespace App\Factories\AssetsSyncRequest;
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
use App\Events\FileCreated;
use Illuminate\Support\Facades\Config;
use models\main\AssetsSyncRequest;
/**
 * Class FileCreatedAssetSyncRequestFactory
 * @package App\Factories\AssetsSyncRequest
 */
final class FileCreatedAssetSyncRequestFactory
{
    public static function build(FileCreated $event){
        $folder_name        = $event->getFolderName();
        $file_name          = $event->getFileName();
        $remote_base_path   = Config::get('scp.scp_remote_base_path', null);
        $remote_destination = sprintf("%s/%s", $remote_base_path, $file_name);
        $asset_sync_request = new AssetsSyncRequest();
        $asset_sync_request->setFrom($remote_destination);
        $asset_sync_request->setTo(sprintf("%s/%s", $folder_name, $file_name));
        $asset_sync_request->setProcessed(false);
        return $asset_sync_request;
    }
}