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
 * Class TrackGroupAction
 * @package App\Events
 */
abstract class TrackGroupAction
{
    use SerializesModels;

    /**
     * @var int
     */
    protected $track_group_id;

    /**
     * @var int
     */
    protected $summit_id;

    /**
     * @var string
     */
    protected $class_name;

    /**
     * TrackGroupAction constructor.
     * @param int $track_group_id
     * @param int $summit_id
     * @param string $class_name
     */
    public function __construct($track_group_id, $summit_id, $class_name)
    {
        $this->track_group_id = $track_group_id;
        $this->summit_id = $summit_id;
        $this->class_name = $class_name;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->class_name;
    }

    /**
     * @return int
     */
    public function getTrackGroupId()
    {
        return $this->track_group_id;
    }

    /**
     * @return int
     */
    public function getSummitId()
    {
        return $this->summit_id;
    }
}