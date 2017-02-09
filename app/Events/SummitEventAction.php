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


use Illuminate\Queue\SerializesModels;

/**
 * Class SummitEventAction
 * @package App\Events
 */
class SummitEventAction extends Event
{

    use SerializesModels;

    /**
     * @var int
     */
    protected $event_id;

    /**
     * SummitEventAction constructor.
     * @param int $event_id
     */
    function __construct($event_id)
    {
        $this->event_id = $event_id;
    }

    /**
     * @return int
     */
    public function getEventId(){ return $this->event_id;}
}