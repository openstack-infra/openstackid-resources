<?php namespace services\utils;
use libs\utils\IEncryptionService;

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

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Str;

/**
 * Class EncryptionService
 * @package services\utils
 */
final class EncryptionService implements IEncryptionService
{
    /**
     * @var Encrypter
     */
    private $enc;

    public function __construct($key, $cipher)
    {
        if (Str::startsWith($key = $key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $this->enc = new Encrypter($key, $cipher);
    }

    public function encrypt($value)
    {
        return $this->enc->encrypt($value);
    }

    public function decrypt($payload)
    {
        return $this->enc->decrypt($payload);
    }
}