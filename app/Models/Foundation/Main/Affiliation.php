<?php namespace models\main;
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

use Doctrine\ORM\Mapping as ORM;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="Affiliation")
 * Class Affiliation
 * @package models\main
 */
class Affiliation extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="StartDate", type="datetime")
     * @var \DateTime
     */
    private $start_date;

    /**
     * @ORM\Column(name="EndDate", type="datetime")
     * @var \DateTime
     */
    private $end_date;

    /**
     * @ORM\Column(name="Current", type="boolean")
     * @var bool
     */
    private $is_current;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", inversedBy="affiliations")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Organization")
     * @ORM\JoinColumn(name="OrganizationID", referencedColumnName="ID")
     * @var Organization
     */
    private $organization;

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param \DateTime $start_date
     */
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @param \DateTime $end_date
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    /**
     * @return bool
     */
    public function isCurrent()
    {
        return $this->is_current;
    }

    public function getIsCurrent(){
        return $this->isCurrent();
    }

    /**
     * @param bool $is_current
     */
    public function setIsCurrent($is_current)
    {
        $this->is_current = $is_current;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getOwnerId(){
        try {
            return $this->owner->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return int
     */
    public function getOrganizationId(){
        try {
            return $this->organization->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasOrganization(){
        return $this->getOrganizationId() > 0;
    }

}