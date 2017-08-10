<?php namespace models\summit\CalendarSync;
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

use Doctrine\ORM\Mapping AS ORM;
use services\utils\Facades\Encryption;

/**
 * Class CalendarSyncInfoOAuth2
 * @ORM\Entity
 * @ORM\Table(name="CalendarSyncInfoOAuth2")
 * @package models\summit\CalendarSync
 */
final class CalendarSyncInfoOAuth2 extends CalendarSyncInfo
{
    /**
     * @ORM\Column(name="AccessToken", type="string")
     * @var string
     */
    protected $access_token;

    /**
     * @ORM\Column(name="RefreshToken", type="string")
     * @var string
     */
    protected $refresh_token;

    /**
     * @return string
     */
    public function getAccessToken()
    {
        $access_token = Encryption::decrypt($this->access_token);
        return json_decode($access_token, true);
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = Encryption::encrypt
        (
            json_encode($access_token)
        );
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return Encryption::decrypt($this->refresh_token);
    }

    /**
     * @param string $refresh_token
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = Encryption::encrypt($refresh_token);
    }

}