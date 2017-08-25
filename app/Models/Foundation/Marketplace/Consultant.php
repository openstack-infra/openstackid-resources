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
 * @ORM\Entity(repositoryClass="App\Repositories\Marketplace\DoctrineConsultantRepository")
 * @ORM\Table(name="Consultant")
 * Class Consultant
 * @package App\Models\Foundation\Marketplace
 */
class Consultant extends RegionalSupportedCompanyService
{

    /**
     * @ORM\OneToMany(targetEntity="Office", mappedBy="consultant", cascade={"persist"}, orphanRemoval=true)
     * @var Office[]
     */
    private $offices;

    /**
     * Consultant constructor.
     */
    public function __construct()
    {
        $this->offices = new ArrayCollection();
    }

    /**
     * @return Office[]
     */
    public function getOffices()
    {
        return $this->offices->toArray();
    }
}