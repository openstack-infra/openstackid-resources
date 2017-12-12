<?php namespace App\Events;
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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Illuminate\Queue\SerializesModels;
use models\summit\PresentationSpeaker;
/**
 * Class PresentationSpeakerEntityStateChanged
 * @package App\Events
 */
class PresentationSpeakerEntityStateChanged extends Event
{
    use SerializesModels;


    /**
     * @var PresentationSpeaker
     */
    protected $speaker;

    /**
     * @var LifecycleEventArgs
     */
    protected $args;

    /**
     * SummitEventEntityStateChanged constructor.
     * @param PresentationSpeaker $speaker
     * @param LifecycleEventArgs $args
     */
    public function __construct(PresentationSpeaker $speaker, LifecycleEventArgs $args)
    {
        $this->speaker = $speaker;
        $this->args    = $args;
    }

    /**
     * @return PresentationSpeaker
     */
    public function getPresentationSpeaker()
    {
        return $this->speaker;
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getArgs()
    {
        return $this->args;
    }
}