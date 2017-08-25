<?php namespace App\Models\Foundation\Marketplace;
/**
 * Copyright 2017 OpenStack Foundation
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
 * @ORM\Table(name="DataCenterLocation")
 * Class DataCenterLocation
 * @package App\Models\Foundation\Marketplace
 */
class DataCenterLocation extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="City", type="string")
     * @var string
     */
    private $city;

    /**
     * @ORM\Column(name="State", type="string")
     * @var string
     */
    private $state;

    /**
     * @ORM\Column(name="Country", type="string")
     * @var string
     */
    private $country;

    /**
     * @ORM\Column(name="Lat", type="float")
     * @var float
     */
    private $lat;

    /**
     * @ORM\Column(name="Lng", type="float")
     * @var float
     */
    private $lng;

    /**
     * @ORM\ManyToOne(targetEntity="CloudService",inversedBy="data_centers", fetch="LAZY")
     * @ORM\JoinColumn(name="CloudServiceID", referencedColumnName="ID")
     * @var CloudService
     */
    private $cloud_service;

    /**
     * @ORM\ManyToOne(targetEntity="DataCenterRegion",inversedBy="locations", fetch="LAZY")
     * @ORM\JoinColumn(name="DataCenterRegionID", referencedColumnName="ID")
     * @var DataCenterRegion
     */
    private $region;

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @return CloudService
     */
    public function getCloudService()
    {
        return $this->cloud_service;
    }

    /**
     * @return DataCenterRegion
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return int
     */
    public function getRegionId(){
        try{
            return !is_null($this->region) ? $this->region->getId(): 0;
        }
        catch (\Exception $ex){
            return 0;
        }
    }
}