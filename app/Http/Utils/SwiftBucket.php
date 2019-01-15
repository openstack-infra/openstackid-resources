<?php namespace App\Http\Utils;
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
use models\main\File;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use OpenStack\OpenStack;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Facades\Config;
use Exception;
/**
 * Class SwiftBucket
 * @package App\Http\Utils
 */
final class SwiftBucket implements IBucket
{
    /**
     * @var StorageObject
     */
    protected $container;

    /**
     * @return \OpenStack\ObjectStore\v1\Models\Container|StorageObject
     */
    protected function getContainer()
    {
        if (!isset($this->container)) {

            $configOptions = [
                'authUrl' => Config::get("cloudstorage.auth_url"),
                'region' => Config::get("cloudstorage.region"),
            ];

            $userName = Config::get("cloudstorage.user_name");
            $userPassword = Config::get("cloudstorage.api_key");

            if(!empty($userName) && !empty($userPassword)){
                $configOptions['user'] = [
                    'name' => $userName,
                    'password' => $userPassword,
                    'domain' => ['id' => Config::get("cloudstorage.user_domain", "default")]
                ];

                $configOptions['scope' ] =  [
                    'project' => [
                        'name' =>  Config::get("cloudstorage.project_name"),
                        'domain' => ['id' => Config::get("cloudstorage.project_domain", "default")]
                    ],
                ];
            }

            $appCredentialId =  Config::get("cloudstorage.app_credential_id");
            $appCredentialSecret = Config::get("cloudstorage.app_credential_secret");

            if(isset($this->config[self::APP_CRED_ID])){
                $configOptions['application_credential'] = [
                    'id' => $appCredentialId,
                    'secret' => $appCredentialSecret,
                ];
            }

            $openstack = new OpenStack($configOptions);

            $this->container = $openstack->objectStoreV1()->getContainer( Config::get("cloudstorage.container"));
        }

        return $this->container;
    }

    /**
     * @param File $f
     * @param string $local_path
     * @return object|StorageObject
     * @throws Exception
     */
    public function put(File $f, $local_path)
    {

        $fp = fopen($local_path, 'r');
        if (!$fp) {
            throw new Exception("Unable to open file: " . $f->getFilename());
        }

        $options = [
            'name'   =>  $f->getRelativeLinkFor(),
            'stream' =>  new Stream($fp)
        ];

        return $this->getContainer()->createObject($options);
    }


}