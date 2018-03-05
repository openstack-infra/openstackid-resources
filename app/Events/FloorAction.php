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
 * Class FloorAction
 * @package App\Events
 */
class FloorAction
{

    use SerializesModels;

    /**
     * @var int
     */
    private $floor_id;

    /**
     * @var int
     */
    private $venue_id;

    /**
     * @var int
     */
    private $summit_id;

    /**
     * FloorAction constructor.
     * @param int $summit_id
     * @param int $venue_id
     * @param int $floor_id
     */
    public function __construct($summit_id, $venue_id, $floor_id)
    {
        $this->summit_id = $summit_id;
        $this->venue_id  = $venue_id;
        $this->floor_id  = $floor_id;
    }

    /**
     * @return int
     */
    public function getFloorId()
    {
        return $this->floor_id;
    }

    /**
     * @return int
     */
    public function getVenueId()
    {
        return $this->venue_id;
    }

    public function getSummitId(){
        return $this->summit_id;
    }

}