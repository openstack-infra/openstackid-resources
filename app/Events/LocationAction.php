<?php namespace App\Events;
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
use Illuminate\Queue\SerializesModels;
/**
 * Class LocationAction
 * @package App\Events
 */
class LocationAction
{
    use SerializesModels;

    /**
     * @var int
     */
    private $location_id;

    /**
     * @var int
     */
    private $summit_id;

    /**
     * @var string
     */
    private $location_class_name;

    /**
     * @var int[]
     */
    private $related_event_ids;

    /**
     * LocationUpdated constructor.
     * @param int $summit_id
     * @param int $location_id
     * @param string $location_class_name
     * @param int[] $related_event_ids
     */
    public function __construct
    (
        $summit_id,
        $location_id,
        $location_class_name,
        array $related_event_ids = []
    )
    {
        $this->summit_id           = $summit_id;
        $this->location_id         = $location_id;
        $this->location_class_name = $location_class_name;
        $this->related_event_ids   = $related_event_ids;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * @return int[]
     */
    public function getRelatedEventIds()
    {
        return $this->related_event_ids;
    }

    /**
     * @return string
     */
    public function getLocationClassName()
    {
        return $this->location_class_name;
    }

    /**
     * @return int
     */
    public function getSummitId()
    {
        return $this->summit_id;
    }
}