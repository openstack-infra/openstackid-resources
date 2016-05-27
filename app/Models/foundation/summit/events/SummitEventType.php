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

use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEventType")
 * Class SummitEventType
 * @package models\summit
 */
class SummitEventType extends SilverstripeBaseModel
{
    use SummitOwned;

    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="Color", type="string")
     * @var string
     */
    private $color;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return bool
     */
    public function getBlackoutTimes()
    {
        return $this->blackout_times;
    }

    /**
     * @param bool $blackout_times
     */
    public function setBlackoutTimes($blackout_times)
    {
        $this->blackout_times = $blackout_times;
    }

    /**
     * @ORM\Column(name="BlackoutTimes", type="boolean")
     * @var bool
     */
    private $blackout_times;

}