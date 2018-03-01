<?php namespace models\summit;
/**
 * Copyright 2015 OpenStack Foundation
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
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="SummitExternalLocation")
 * Class SummitExternalLocation
 * @package models\summit
 */
class SummitExternalLocation extends SummitGeoLocatedLocation
{
    const Bar    = 'Bar';
    const Lounge = 'Lounge';
    const Other  = 'Other';

    const ClassName = 'SummitExternalLocation';

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    /**
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param int $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    /**
     * @ORM\Column(name="Capacity", type="integer")
     */
    protected $capacity;

    public static $metadata = [
        'class_name'   => self::ClassName,
        'capacity'     => 'integer',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(SummitGeoLocatedLocation::getMetadata(), self::$metadata);
    }

    public function __construct()
    {
        parent::__construct();
        $this->type     = self::TypeExternal;
        $this->capacity = 0;
    }
}