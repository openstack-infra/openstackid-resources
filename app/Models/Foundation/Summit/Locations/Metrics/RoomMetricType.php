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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping AS ORM;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="RoomMetricType")
 * Class SummitVenueRoom
 * @package RoomMetricType\summit
 */
class RoomMetricType extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="Endpoint", type="string")
     * @var string
     */
    private $endpoint;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitVenueRoom", inversedBy="metrics")
     * @ORM\JoinColumn(name="RoomID", referencedColumnName="ID")
     * @var SummitVenueRoom
     */
    private $room;

    /**
     * @ORM\OneToMany(targetEntity="RoomMetricSampleData", mappedBy="type", cascade={"persist"})
     */
    private $samples;


    /**
     * RoomMetricType constructor.
     */
    public function __construct()
    {
        $this->samples = new ArrayCollection();
    }

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
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @return SummitVenueRoom
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param SummitVenueRoom $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
    }

    /**
     * @return ArrayCollection
     */
    public function getSamples()
    {
        return $this->samples;
    }

    /**
     * @param ArrayCollection $samples
     */
    public function setSamples($samples)
    {
        $this->samples = $samples;
    }

    /**
     * @param int $epoch_start_date
     * @param int $epoch_end_date
     * @return float
     */
    public function getMaxValueByTimeWindow($epoch_start_date, $epoch_end_date){
        $samples = $this->getValuesByTimeWindow($epoch_start_date, $epoch_end_date, 'value', Criteria::DESC);
        $max     = count($samples) > 0 ? $samples[0] : null;
        return !is_null($max) ? $max->getValue() : 0.0;
    }

    /**
     * @param int $epoch_start_date
     * @param int $epoch_end_date
     * @return float
     */
    public function getMinValueByTimeWindow($epoch_start_date, $epoch_end_date){
        $samples = $this->getValuesByTimeWindow($epoch_start_date, $epoch_end_date, 'value', Criteria::ASC);
        $min     = count($samples) > 0 ? $samples[0] : null;
        return !is_null($min) ? $min->getValue() : 0.0;
    }

    /**
     * @param int $epoch_start_date
     * @param int $epoch_end_date
     * @return float
     */
    public function getCurrentValueByTimeWindow($epoch_start_date, $epoch_end_date){
       $utc_epoch     = time();
       $rounded_epoch = $utc_epoch- $utc_epoch % 60;
       $res           = 0.0;
       if($epoch_start_date <= $rounded_epoch && $rounded_epoch <= $epoch_end_date){
           $samples = $this->getValuesByTimeWindow($epoch_start_date, $epoch_end_date, 'timestamp', Criteria::ASC);
           foreach($samples as $sample){
               if($sample->getTimestamp() == $rounded_epoch)
                   $res = $sample->getValue();
           }
       }
       return $res;
    }

    /**
     * @param int $epoch_start_date
     * @param int $epoch_end_date
     * @return float
     */
    public function getMeanValueByTimeWindow($epoch_start_date, $epoch_end_date){
        $samples = $this->getValuesByTimeWindow($epoch_start_date, $epoch_end_date, 'value', Criteria::ASC);
        $sum     = 0.0;
        foreach($samples as $sample){
            $sum += $sample->getValue();
        }
        return count($samples) > 0 ? $sum / count($samples) : 0.0;
    }

    /**
     * @param int $epoch_start_date
     * @param int $epoch_end_date
     * @param string|null $order_by
     * @param string|null $order_by_direction
     * @return array
     */
    public function getValuesByTimeWindow($epoch_start_date, $epoch_end_date, $order_by = null, $order_by_direction = null){
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gte("timestamp", $epoch_start_date))
            ->andWhere(Criteria::expr()->lte("timestamp", $epoch_end_date));

        if(!is_null($order_by) && !is_null($order_by_direction)){
            $criteria = $criteria->orderBy(array($order_by => $order_by_direction));
        }

        $samples = $this->samples->matching($criteria);
        return $samples->toArray();
    }
}