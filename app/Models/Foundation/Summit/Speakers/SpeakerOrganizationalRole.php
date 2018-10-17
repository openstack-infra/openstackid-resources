<?php namespace models\summit;
/**
 * Copyright 2018 OpenStack Foundation
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
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSpeakerOrganizationalRoleRepository")
 * @ORM\Table(name="SpeakerOrganizationalRole")
 * Class SpeakerOrganizationalRole
 * @package models\summit
 */
class SpeakerOrganizationalRole extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Role", type="string")
     */
    private $role;

    /**
     * @ORM\Column(name="IsDefault", type="boolean")
     */
    private $is_default;

    /**
     * SpeakerOrganizationalRole constructor.
     * @param string $role
     * @param bool $is_default
     */
    public function __construct($role, $is_default = false)
    {
        parent::__construct();
        $this->role = $role;
        $this->is_default = $is_default;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * @param bool $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }



}