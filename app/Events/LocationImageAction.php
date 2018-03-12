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
 * Class LocationImageAction
 * @package App\Events
 */
class LocationImageAction
{
    use SerializesModels;

    /**
     * @var int
     */
    protected $entity_id;
    /**
     * @var int
     */
    protected $location_id;

    /**
     * @var string
     */
    protected $image_type;

    /**
     * @var int
     */
    protected $summit_id;

    /**
     * LocationImageAction constructor.
     * @param int $entity_id
     * @param int $location_id
     * @param int $summit_id
     * @param string $image_type
     */
    public function __construct($entity_id, $location_id, $summit_id, $image_type)
    {
        $this->entity_id   = $entity_id;
        $this->location_id = $location_id;
        $this->summit_id   = $summit_id;
        $this->image_type  = $image_type;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * @return string
     */
    public function getImageType()
    {
        return $this->image_type;
    }

    /**
     * @return int
     */
    public function getSummitId(){
        return $this->summit_id;
    }

    /**
     * @return int
     */
    public function getEntityId(){
        return $this->entity_id;
    }

}