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
use App\Models\Foundation\Software\OpenStackComponent;
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
     * @ORM\OneToMany(targetEntity="ConsultantClient", mappedBy="consultant", cascade={"persist"}, orphanRemoval=true)
     * @var ConsultantClient[]
     */
    private $clients;

    /**
     * @ORM\ManyToMany(targetEntity="SpokenLanguage", cascade={"persist"})
     * @ORM\JoinTable(name="Consultant_SpokenLanguages",
     *      joinColumns={@ORM\JoinColumn(name="ConsultantID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="SpokenLanguageID", referencedColumnName="ID")}
     *      )
     * @var SpokenLanguage[]
     */
    private $spoken_languages;

    /**
     * @ORM\ManyToMany(targetEntity="ConfigurationManagementType", cascade={"persist"})
     * @ORM\JoinTable(name="Consultant_ConfigurationManagementExpertises",
     *      joinColumns={@ORM\JoinColumn(name="ConsultantID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="ConfigurationManagementTypeID", referencedColumnName="ID")}
     *      )
     * @var ConfigurationManagementType[]
     */
    private $configuration_management_expertise;

    /**
     * @ORM\ManyToMany(targetEntity="App\Models\Foundation\Software\OpenStackComponent", cascade={"persist"})
     * @ORM\JoinTable(name="Consultant_ExpertiseAreas",
     *      joinColumns={@ORM\JoinColumn(name="ConsultantID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="OpenStackComponentID", referencedColumnName="ID")}
     *      )
     * @var OpenStackComponent[]
     */
    private $expertise_areas;

    /**
     * @ORM\OneToMany(targetEntity="ConsultantServiceOfferedType", mappedBy="consultant", cascade={"persist"}, orphanRemoval=true)
     * @var ConsultantServiceOfferedType[]
     */
    private $services_offered;

    /**
     * Consultant constructor.
     */
    public function __construct()
    {
        $this->offices                             = new ArrayCollection();
        $this->clients                             = new ArrayCollection();
        $this->spoken_languages                    = new ArrayCollection();
        $this->configuration_management_expertises = new ArrayCollection();
        $this->expertise_areas                     = new ArrayCollection();
        $this->services_offered                    = new ArrayCollection();
    }

    /**
     * @return Office[]
     */
    public function getOffices()
    {
        return $this->offices->toArray();
    }

    /**
     * @return ConsultantClient[]
     */
    public function getClients(){
        return $this->clients->toArray();
    }

    /**
     * @return SpokenLanguage[]
     */
    public function getSpokenLanguages()
    {
        return $this->spoken_languages->toArray();
    }

    /**
     * @return ConfigurationManagementType[]
     */
    public function getConfigurationManagementExpertise()
    {
        return $this->configuration_management_expertise->toArray();
    }

    /**
     * @return OpenStackComponent[]
     */
    public function getExpertiseAreas()
    {
        return $this->expertise_areas->toArray();
    }

    /**
     * @return ConsultantServiceOfferedType[]
     */
    public function getServicesOffered()
    {
        return $this->services_offered->toArray();
    }
}