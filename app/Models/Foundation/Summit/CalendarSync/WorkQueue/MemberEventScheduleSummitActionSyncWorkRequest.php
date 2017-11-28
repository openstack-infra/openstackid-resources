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
 * Class MemberEventScheduleSummitActionSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="MemberEventScheduleSummitActionSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
class MemberEventScheduleSummitActionSyncWorkRequest
extends MemberScheduleSummitActionSyncWorkRequest
{
    const SubType = 'EVENT';

    /**
     * @ORM\Column(name="SummitEventID", type="integer")
     * @var int
     */
    private $summit_event_id;

    /**
     * @return mixed
     */
    public function getSummitEventId()
    {
        return $this->summit_event_id;
    }

    /**
     * @param mixed $summit_event_id
     */
    public function setSummitEventId($summit_event_id)
    {
        $this->summit_event_id = $summit_event_id;
    }

    /**
     * @return SummitEvent
     */
    public function getSummitEvent()
    {
        $id = $this->summit_event_id;
        try {
            $event = $this->getEM()->find(SummitEvent::class, $id);
        }
        catch(\Exception $ex){
            return null;
        }
        return $event;
    }

    /**
     * @param SummitEvent $summit_event
     */
    public function setSummitEvent($summit_event)
    {
        $this->summit_event_id = $summit_event->getId();
    }
    /**
     * @return string
     */
    public function getSubType(){
        return self::SubType;
    }
}