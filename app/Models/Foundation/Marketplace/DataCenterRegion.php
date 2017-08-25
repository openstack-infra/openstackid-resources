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
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity
 * @ORM\Table(name="DataCenterRegion")
 * Class DataCenterRegion
 * @package App\Models\Foundation\Marketplace
 */
class DataCenterRegion extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="Endpoint", type="string")
     * @var string
     */
    private $endpoint;

    /**
     * @ORM\Column(name="Color", type="string")
     * @var string
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity="DataCenterLocation", mappedBy="region", cascade={"persist"}, orphanRemoval=true)
     * @var DataCenterLocation[]
     */
    private $locations;

    /**
     * @ORM\ManyToOne(targetEntity="CloudService",inversedBy="data_center_regions", fetch="LAZY")
     * @ORM\JoinColumn(name="CloudServiceID", referencedColumnName="ID")
     * @var CloudService
     */
    private $cloud_service;

    public function __construct()
    {
        parent::__construct();
        $this->locations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return DataCenterLocation[]
     */
    public function getLocations()
    {
        return $this->locations->toArray();
    }

    /**
     * @return CloudService
     */
    public function getCloudService()
    {
        return $this->cloud_service;
    }
}