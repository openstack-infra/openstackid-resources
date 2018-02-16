<?php namespace models\main;
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
use Doctrine\ORM\Mapping AS ORM;
use models\summit\CalendarSync\CalendarSyncInfo;
/**
 * @ORM\Entity
 * @ORM\Table(name="CalendarSyncErrorEmailRequest")
 * Class CalendarSyncErrorEmailRequest
 * @package models\main
 */
class CalendarSyncErrorEmailRequest extends EmailCreationRequest
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\CalendarSync\CalendarSyncInfo")
     * @ORM\JoinColumn(name="CalendarSyncInfoID", referencedColumnName="ID")
     * @var CalendarSyncInfo
     */
    protected $sync_info;

    /**
     * @return CalendarSyncInfo
     */
    public function getSyncInfo()
    {
        return $this->sync_info;
    }

    /**
     * @param CalendarSyncInfo $sync_info
     */
    public function setSyncInfo($sync_info)
    {
        $this->sync_info = $sync_info;
    }
}