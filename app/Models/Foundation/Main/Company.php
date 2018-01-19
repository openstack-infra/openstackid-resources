<?php namespace models\main;
/**
 * Copyright 2015 OpenStack Foundation
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
 * @ORM\Entity(repositoryClass="repositories\main\DoctrineCompanyRepository")
 * @ORM\Table(name="Company")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="sponsors_region")
 * Class Company
 * @package models\main
 */
class Company extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\SummitEvent", mappedBy="sponsors")
     */
    private $sponsorships;

    public function __construct()
    {
        parent::__construct();
        $this->sponsorships = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}