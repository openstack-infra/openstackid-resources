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

    /**
     * @return \DateTime
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


}