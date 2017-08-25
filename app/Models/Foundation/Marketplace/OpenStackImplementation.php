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
use App\Models\Foundation\Software\OpenStackRelease;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use DateTime;
use DateTimeZone;
/**
 * @ORM\Entity
 * @ORM\Table(name="OpenStackImplementation")
 * Class OpenStackImplementation
 * @package App\Models\Foundation\Marketplace
 */
class OpenStackImplementation extends RegionalSupportedCompanyService
{
    /**
     * @ORM\Column(name="CompatibleWithStorage", type="boolean")
     * @var bool
     */
    protected $is_compatible_with_storage;

    /**
     * @ORM\Column(name="CompatibleWithCompute", type="boolean")
     * @var bool
     */
    protected $is_compatible_with_compute;

    /**
     * @ORM\Column(name="CompatibleWithFederatedIdentity", type="boolean")
     * @var bool
     */
    protected $is_compatible_with_federated_identity;

    /**
     * @ORM\Column(name="ExpiryDate", type="datetime")
     * @var \DateTime
     */
    protected $expire_date;

    /**
     * @ORM\Column(name="Notes", type="string")
     * @var string
     */
    protected $notes;

    /**
     * @ORM\ManyToOne(targetEntity="InteropProgramVersion", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ProgramVersionID", referencedColumnName="ID")
     * @var InteropProgramVersion
     */
    protected $program_version;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\Foundation\Software\OpenStackRelease", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="ReportedReleaseID", referencedColumnName="ID")
     * @var OpenStackRelease
     */
    protected $reported_release;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\Foundation\Software\OpenStackRelease", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="PassedReleaseID", referencedColumnName="ID")
     * @var OpenStackRelease
     */
    protected $passed_release;

    /**
     * @ORM\OneToMany(targetEntity="OpenStackImplementationApiCoverage", mappedBy="implementation", cascade={"persist"})
     * @var OpenStackImplementationApiCoverage[]
     */
    protected $capabilities;

    /**
     * @ORM\ManyToMany(targetEntity="HyperVisorType", cascade={"persist"})
     * @ORM\JoinTable(name="OpenStackImplementation_HyperVisors",
     *      joinColumns={@ORM\JoinColumn(name="OpenStackImplementationID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="HyperVisorTypeID", referencedColumnName="ID")}
     *     )
     * @var HyperVisorType[]
     */
    protected $hypervisors;

    /**
     * @ORM\ManyToMany(targetEntity="GuestOSType", cascade={"persist"})
     * @ORM\JoinTable(name="OpenStackImplementation_Guests",
     *      joinColumns={@ORM\JoinColumn(name="OpenStackImplementationID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="GuestOSTypeID", referencedColumnName="ID")}
     *      )
     * @var GuestOSType[]
     */
    protected $guests;

    /**
     * OpenStackImplementation constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->hypervisors  = new ArrayCollection();
        $this->guests       = new ArrayCollection();
        $this->capabilities = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isCompatibleWithStorage()
    {
        return $this->is_compatible_with_storage;
    }

    /**
     * @return bool
     */
    public function isCompatibleWithCompute()
    {
        return $this->is_compatible_with_compute;
    }

    /**
     * @return bool
     */
    public function isCompatibleWithFederatedIdentity()
    {
        return $this->is_compatible_with_federated_identity;
    }

    /***
     * @return bool
     */
    public function isCompatibleWithPlatform()
    {
        return $this->isCompatibleWithStorage() && $this->isCompatibleWithCompute();
    }

    /***
     * @return bool
     */
    public function isOpenStackPowered()
    {
        $storage  = $this->isCompatibleWithStorage();
        $compute  = $this->isCompatibleWithCompute();
        $platform = $this->isCompatibleWithPlatform();
        return ($storage || $compute || $platform) && !$this->isOpenStackPoweredExpired();
    }

    /**
     * @return bool
     */
    public function isOpenStackPoweredExpired()
    {
        $res = false;
        if(!$this->expire_date) return $res;
        $utc_timezone = new DateTimeZone("UTC");
        $time_zone    = new DateTimeZone('America/Chicago');
        $expiry_date  = new DateTime($this->expire_date->format(DateTime::ISO8601), $time_zone);
        $expiry_date  = $expiry_date->setTimezone($utc_timezone);
        $utc_now      = new DateTime(null, new DateTimeZone("UTC"));
        return $utc_now > $expiry_date;
    }

    /**
     * @return string
     */
    public function getTestedCapabilityTypeLabel()
    {
        if ($this->isCompatibleWithPlatform()) {
            return 'Platform';
        } else if ($this->isCompatibleWithCompute()) {
            return 'Compute';
        } else if ($this->isCompatibleWithStorage()) {
            return 'Storage';
        }
    }

    /**
     * @return bool
     */
    public function isOpenStackTested()
    {
        try {
            $program_version = $this->program_version;
            return !is_null($program_version) && $program_version->getId() > 0;
        }
        catch(\Exception $ex){
            return false;
        }
    }

    /**
     * @return null|string
     */
    public function getOpenStackTestedLabel(){
        if(!$this->isOpenStackTested()) return null;
        return $this->getTestedCapabilityTypeLabel().' '.$this->program_version->getName();
    }
    /**
     * @return DateTime
     */
    public function getExpireDate()
    {
        return $this->expire_date;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return OpenStackImplementationApiCoverage
     */
    public function getCapabilities()
    {
        return $this->capabilities->toArray();
    }

    /**
     * @return HyperVisorType[]
     */
    public function getHypervisors()
    {
        return $this->hypervisors->toArray();
    }

    /**
     * @return GuestOSType[]
     */
    public function getGuests()
    {
        return $this->guests->toArray();
    }
}