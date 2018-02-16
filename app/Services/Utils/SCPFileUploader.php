<?php namespace App\Services\Utils;
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
use IDCT\Networking\Ssh\Credentials;
use IDCT\Networking\Ssh\SftpClient;
use Illuminate\Support\Facades\Config;


/**
 * Class SCPFileUploader
 * @package App\Services\Utils
 */
final class SCPFileUploader
{
    public static function upload(FileCreated $event){
        $storage_path      = storage_path();
        $local_path        = $event->getLocalPath();
        $file_name         = $event->getFileName();
        $remote_base_path  = Config::get('scp.scp_remote_base_path', null);
        $client            = new SftpClient();
        $host              = Config::get('scp.scp_host', null);

        $credentials       = Credentials::withPublicKey
        (
            Config::get('scp.ssh_user', null),
            Config::get('scp.ssh_public_key', null),
            Config::get('scp.ssh_private_key', null)
        );

        $client->setCredentials($credentials);
        $client->connect($host);
        $remote_destination = sprintf("%s/%s", $remote_base_path, $file_name);
        $client->scpUpload(sprintf("%s/app/%s", $storage_path, $local_path), $remote_destination);
        $client->close();
    }
}