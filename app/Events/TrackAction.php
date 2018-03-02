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
 * Class TrackAction
 * @package App\Events
 */
class TrackAction
{
    use SerializesModels;

    /**
     * @var int
     */
    private $track_id;

    /**
     * @var int
     */
    private $summit_id;

    /**
     * TrackAction constructor.
     * @param int $summit_id
     * @param int $track_id
     */
    public function __construct($summit_id, $track_id)
    {
        $this->summit_id = $summit_id;
        $this->track_id  = $track_id;
    }

    /**
     * @return int
     */
    public function getTrackId()
    {
        return $this->track_id;
    }

    /**
     * @return int
     */
    public function getSummitId()
    {
        return $this->summit_id;
    }
}