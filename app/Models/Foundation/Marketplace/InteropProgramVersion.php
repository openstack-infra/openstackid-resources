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
 * @ORM\Table(name="InteropProgramVersion")
 * Class InteropProgramVersion
 * @package App\Models\Foundation\Marketplace
 */
class InteropProgramVersion extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="InteropCapability", cascade={"persist"})
     * @ORM\JoinTable(name="nteropProgramVersion_Capabilities",
     *      joinColumns={@ORM\JoinColumn(name="InteropProgramVersionID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="InteropCapabilityID", referencedColumnName="ID")}
     *      )
     * @var SupportChannelType[]
     */
    private $capabilities;


    /**
     * @ORM\ManyToMany(targetEntity="InteropDesignatedSection", cascade={"persist"})
     * @ORM\JoinTable(name="InteropProgramVersion_DesignatedSections",
     *      joinColumns={@ORM\JoinColumn(name="InteropProgramVersionID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="InteropDesignatedSectionID", referencedColumnName="ID")}
     *      )
     * @var InteropDesignatedSection[]
     */
    private $designated_sections;

    public function __construct()
    {
        $this->capabilities        = new ArrayCollection();
        $this->designated_sections = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }



}