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

/**
 * Class CalendarSyncInfoCalDav
 * @ORM\Entity
 * @ORM\Table(name="CalendarSyncInfoCalDav")
 * @package models\summit\CalendarSync
 */
final class CalendarSyncInfoCalDav extends CalendarSyncInfo
{
    /**
     * @ORM\Column(name="UserName", type="string")
     * @var string
     */
    protected $user_name;

    /**
     * @ORM\Column(name="UserPassword", type="string")
     * @var string
     */
    protected $user_password;

    /**
     * @ORM\Column(name="UserPrincipalURL", type="string")
     * @var string
     */
    protected $user_principal_url;

    /**
     * @ORM\Column(name="CalendarDisplayName", type="string")
     * @var string
     */
    protected $calendar_display_name;

    /**
     * @ORM\Column(name="CalendarSyncToken", type="string")
     * @var string
     */
    protected $calendar_sync_token;

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * @param string $user_name
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;
    }

    /**
     * @return string
     */
    public function getUserPassword()
    {
        return $this->user_password;
    }

    /**
     * @param string $user_password
     */
    public function setUserPassword($user_password)
    {
        $this->user_password = $user_password;
    }

    /**
     * @return string
     */
    public function getUserPrincipalUrl()
    {
        return $this->user_principal_url;
    }

    /**
     * @param string $user_principal_url
     */
    public function setUserPrincipalUrl($user_principal_url)
    {
        $this->user_principal_url = $user_principal_url;
    }

    /**
     * @return string
     */
    public function getServer(){
        $result = parse_url($this->user_principal_url);
        return $result['scheme']."://".$result['host'];
    }

    /**
     * @return string
     */
    public function getCalendarUrl(){
        return $this->external_id;
    }

    /**
     * @return string
     */
    public function getCalendarDisplayName()
    {
        return $this->calendar_display_name;
    }

    /**
     * @param string $calendar_display_name
     */
    public function setCalendarDisplayName($calendar_display_name)
    {
        $this->calendar_display_name = $calendar_display_name;
    }

    /**
     * @return string
     */
    public function getCalendarSyncToken()
    {
        return $this->calendar_sync_token;
    }

    /**
     * @param string $calendar_sync_token
     */
    public function setCalendarSyncToken($calendar_sync_token)
    {
        $this->calendar_sync_token = $calendar_sync_token;
    }
}