<?php namespace App\Events;
/**
 * Copyright 2016 OpenStack Foundation
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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Illuminate\Queue\SerializesModels;
use models\summit\SummitEvent;

/**
 * Class SummitEventEntityStateChanged
 * @package App\Events
 */
class SummitEventEntityStateChanged extends Event
{
    use SerializesModels;

    /**
     * @var SummitEvent
     */
    protected $summit_event;

    /**
     * @var LifecycleEventArgs
     */
    protected $args;

    /**
     * SummitEventEntityStateChanged constructor.
     * @param SummitEvent $summit_event
     * @param LifecycleEventArgs $args
     */
    public function __construct(SummitEvent $summit_event, LifecycleEventArgs $args)
    {
        $this->summit_event = $summit_event;
        $this->args         = $args;
    }

    /**
     * @return SummitEvent
     */
    public function getSummitEvent()
    {
        return $this->summit_event;
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getArgs()
    {
        return $this->args;
    }

}