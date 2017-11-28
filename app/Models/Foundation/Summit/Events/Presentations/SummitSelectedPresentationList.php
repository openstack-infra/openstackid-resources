<?php namespace models\summit;
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

use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use models\main\Member;
/**
 * Class SummitSelectedPresentationList
 * @ORM\Entity
 * @ORM\Table(name="SummitSelectedPresentationList")
 * @package models\summit
 */
class SummitSelectedPresentationList extends SilverstripeBaseModel
{
    const Individual = 'Individual';
    const Group      = 'Group';
    const Session    = 'Session';
    const Lightning  = 'Lightning';

    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="ListType", type="string")
     * @var string
     */
    private $list_type;

    /**
     * @ORM\Column(name="ListClass", type="string")
     * @var string
     */
    private $list_class;

    /**
     * @ORM\Column(name="Hash", type="string")
     * @var string
     */
    private $hash;

    /**
     * @ORM\ManyToOne(targetEntity="PresentationCategory", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="CategoryID", referencedColumnName="ID")
     * @var PresentationCategory
     */
    private $category = null;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member = null;

    /**
     * @ORM\OneToMany(targetEntity="SummitSelectedPresentation", mappedBy="list", cascade={"persist"}, orphanRemoval=true)
     * @var SummitSelectedPresentation[]
     */
    private $selected_presentations;


    public function __construct()
    {
        $this->selected_presentations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getListType()
    {
        return $this->list_type;
    }

    /**
     * @param string $list_type
     */
    public function setListType($list_type)
    {
        $this->list_type = $list_type;
    }

    /**
     * @return string
     */
    public function getListClass()
    {
        return $this->list_class;
    }

    /**
     * @param string $list_class
     */
    public function setListClass($list_class)
    {
        $this->list_class = $list_class;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return PresentationCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param PresentationCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }

    /**
     * @return SummitSelectedPresentation[]
     */
    public function getSelectedPresentations()
    {
        return $this->selected_presentations;
    }

    /**
     * @param SummitSelectedPresentation[] $selected_presentations
     */
    public function setSelectedPresentations($selected_presentations)
    {
        $this->selected_presentations = $selected_presentations;
    }
}