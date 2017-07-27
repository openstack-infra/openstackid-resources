<?php namespace models\summit\CalendarSync\WorkQueue;
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

use models\summit\SummitEvent;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Class AdminSummitEventActionSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="AdminSummitEventActionSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
final class AdminSummitEventActionSyncWorkRequest
    extends AdminScheduleSummitActionSyncWorkRequest
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitEvent", cascade={"persist"})
     * @ORM\JoinColumn(name="SummitEventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    private $summit_event;

    /**
     * @return SummitEvent
     */
    public function getSummitEvent()
    {
        return $this->summit_event;
    }

    /**
     * @param SummitEvent $summit_event
     */
    public function setSummitEvent($summit_event)
    {
        $this->summit_event = $summit_event;
    }

}