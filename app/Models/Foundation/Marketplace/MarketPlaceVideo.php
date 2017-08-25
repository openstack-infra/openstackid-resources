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
 * @ORM\Table(name="MarketPlaceVideo")
 * Class MarketPlaceVideo
 * @package App\Models\Foundation\Marketplace
 */
class MarketPlaceVideo extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="YouTubeID", type="string")
     * @var string
     */
    private $youtube_id;

    /**
     * @ORM\ManyToOne(targetEntity="MarketPlaceVideoType", fetch="LAZY")
     * @ORM\JoinColumn(name="TypeID", referencedColumnName="ID")
     * @var MarketPlaceVideoType
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="CompanyService",inversedBy="videos", fetch="LAZY")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var CompanyService
     */
    private $company_service;

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getYoutubeId()
    {
        return $this->youtube_id;
    }

    /**
     * @return MarketPlaceVideoType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return CompanyService
     */
    public function getCompanyService()
    {
        return $this->company_service;
    }
}