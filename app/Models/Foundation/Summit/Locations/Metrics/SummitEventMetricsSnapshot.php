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

/**
 * Class SummitEventMetricsSnapshot
 * @package models\summit
 */
final class SummitEventMetricsSnapshot
{
    /**
     * @var float
     */
    private $average;
    /**
     * @var float
     */
    private $max;
    /**
     * @var float
     */
    private $min;
    /**
     * @var float
     */
    private $current;

    /**
     * @var RoomMetricType
     */
    private $type;

    /**
     * @var SummitEvent
     */
    private $event;

    /**
     * SummitEventMetricsSnapshot constructor.
     * @param SummitEvent $event
     * @param RoomMetricType $type
     * @param $average
     * @param $max
     * @param $min
     * @param $current
     */
    public function __construct(SummitEvent $event, RoomMetricType $type, $average, $max, $min, $current)
    {
        $this->average = $average;
        $this->max     = $max;
        $this->min     = $min;
        $this->current = $current;
        $this->event   = $event;
        $this->type    = $type;
    }

    /**
     * @return RoomMetricType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->type->getType();
    }

    /**
     * @return int
     */
    public function getEventId(){
        return $this->event->getId();
    }

    /**
     * @return float
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @return float
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @return float
     */
    public function getCurrent()
    {
        return $this->current;
    }


}