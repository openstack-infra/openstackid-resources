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

/**
 * Class SummitVenueRoomUpdated
 * @package App\Events
 */
final class SummitVenueRoomUpdated extends LocationAction
{
    /**
     * @var int
     */
    private $old_floor_id;
    /**
     * @var int
     */
    private $new_floor_id;

    /**
     * SummitVenueRoomUpdated constructor.
     * @param int $summit_id
     * @param int $location_id
     * @param array $related_event_ids
     * @param int $old_floor_id
     * @param int $new_floor_id
     */
    public function __construct($summit_id, $location_id, array $related_event_ids = [], $old_floor_id = 0, $new_floor_id = 0)
    {
        parent::__construct(
            $summit_id,
            $location_id,
            'SummitVenueRoom',
            $related_event_ids
        );
        $this->old_floor_id = $old_floor_id;
        $this->new_floor_id = $new_floor_id;
    }

    /**
     * @return int
     */
    public function getOldFloorId()
    {
        return $this->old_floor_id;
    }

    /**
     * @return int
     */
    public function getNewFloorId()
    {
        return $this->new_floor_id;
    }

}