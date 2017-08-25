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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="CloudService")
 * Class CloudService
 * @package App\Models\Foundation\Marketplace
 */
class CloudService extends OpenStackImplementation
{
    /**
     * @ORM\OneToMany(targetEntity="DataCenterLocation", mappedBy="cloud_service", cascade={"persist"}, orphanRemoval=true)
     * @var DataCenterLocation[]
     */
    protected $data_centers;

    /**
     * @ORM\OneToMany(targetEntity="DataCenterRegion", mappedBy="cloud_service", cascade={"persist"}, orphanRemoval=true)
     * @var DataCenterRegion[]
     */
    protected $data_center_regions;

    public function __construct()
    {
        parent::__construct();
        $this->data_centers         = new ArrayCollection();
        $this->capabilities_offered = new ArrayCollection();
        $this->data_center_regions  = new ArrayCollection();
    }

    /**
     * @return DataCenterLocation[]
     */
    public function getDataCenters()
    {
        return $this->data_centers->toArray();
    }

    /**
     * @return DataCenterRegion[]
     */
    public function getDataCenterRegions()
    {
        return $this->data_center_regions->toArray();
    }

}