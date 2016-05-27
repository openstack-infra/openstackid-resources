<?php namespace models\oauth2;

/**
 * Copyright 2015 OpenStack Foundation
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

/**
 * Class Token
 * Defines the common behavior for all emitted tokens
 * @package oauth2\models
 */
abstract class Token
{

    const DefaultByteLength = 32;

    protected $value;
    protected $lifetime;

    protected $client_id;
    protected $len;
    protected $scope;
    protected $audience;
    protected $from_ip;
    protected $is_hashed;
    /**
     * @var null|int
     */
    protected $user_id;

    public function __construct($len = self::DefaultByteLength)
    {
        $this->len = $len;
        $this->is_hashed = false;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLifetime()
    {
        return intval($this->lifetime);
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getAudience()
    {
        return $this->audience;
    }

    public function getFromIp()
    {
        return $this->from_ip;
    }

    /**
     * @return null|int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    public function isHashed()
    {
        return $this->is_hashed;
    }

    public abstract function toJSON();


    public abstract function fromJSON($json);
}