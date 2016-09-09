<?php namespace models\summit;

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


use Doctrine\ORM\Mapping AS ORM;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="RoomMetricSampleData")
 * Class RoomMetricSampleData
 * @package RoomMetricType\summit
 */
final class RoomMetricSampleData extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="TimeStamp", type="integer")
     * @var int
     */
    private $timestamp;

    /**
     * @ORM\Column(name="Value", type="float")
     * @var float
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\RoomMetricType", inversedBy="samples")
     * @ORM\JoinColumn(name="TypeID", referencedColumnName="ID")
     * @var RoomMetricType
     */
    private $type;

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return RoomMetricType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param RoomMetricType $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}