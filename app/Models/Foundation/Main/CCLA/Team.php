<?php namespace Models\Foundation\Main\CCLA;
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
use models\main\Company;
use models\main\Member;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Class Team
 * @ORM\Entity
 * @ORM\Table(name="Team")
 * @package Models\Foundation\Main\CCLA
 */
class Team extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     */
    private $name;

    public function __construct(){
        parent::__construct();
        $this->members  = new ArrayCollection();
    }

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Member", mappedBy="ccla_teams")
     */
    private $members;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Company")
     * @ORM\JoinColumn(name="CompanyID", referencedColumnName="ID")
     * @var Company
     */
    private $company;

    /**
     * @return Company
     */
    public function getCompany(){
        return $this->company;
    }

    /**
     * @return Member[]
     */
    public function getMembers(){
        return $this->members->toArray();
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCompanyId(){
        return $this->company->getId();
    }
}