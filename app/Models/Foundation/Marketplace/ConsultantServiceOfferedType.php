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
use App\Models\Utils\BaseEntity;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="Consultant_ServicesOffered")
 * Class ConsultantServiceOfferedType
 * @package App\Models\Foundation\Marketplace
 */
class ConsultantServiceOfferedType extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Consultant", inversedBy="services_offered")
     * @ORM\JoinColumn(name="ConsultantID", referencedColumnName="ID")
     * @var Consultant
     */
    private $consultant;

    /**
     * @ORM\ManyToOne(targetEntity="ServiceOfferedType", fetch="LAZY")
     * @ORM\JoinColumn(name="ConsultantServiceOfferedTypeID", referencedColumnName="ID")
     * @var ServiceOfferedType
     */
    private $service_offered;

    /**
     * @ORM\ManyToOne(targetEntity="Region", fetch="LAZY")
     * @ORM\JoinColumn(name="RegionID", referencedColumnName="ID")
     * @var Region
     */
    private $region;

    /**
     * @return Consultant
     */
    public function getConsultant()
    {
        return $this->consultant;
    }

    /**
     * @return ServiceOfferedType
     */
    public function getServiceOffered()
    {
        return $this->service_offered;
    }

    /**
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }
}