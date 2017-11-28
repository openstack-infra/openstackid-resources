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
 * Class SummitSelectedPresentation
 * @ORM\Entity
 * @ORM\Table(name="SummitSelectedPresentation")
 * @package models\summit
*/
class SummitSelectedPresentation extends SilverstripeBaseModel
{

    const CollectionSelected = 'selected';
    const CollectionMaybe    = 'maybe';
    const CollectionPass     = 'pass';

    /**
     * @ORM\Column(name="Collection", type="string")
     * @var string
     */
    private $collection;

    /**
     * @ORM\Column(name="`Order`", type="integer")
     * @var int
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="SummitSelectedPresentationList")
     * @ORM\JoinColumn(name="SummitSelectedPresentationListID", referencedColumnName="ID")
     * @var SummitSelectedPresentationList
     */
    private $list;

    /**
     * @ORM\ManyToOne(targetEntity="Presentation")
     * @ORM\JoinColumn(name="PresentationID", referencedColumnName="ID")
     * @var Presentation
     */
    private $presentation;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member = null;

    /**
     * @return string
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return SummitSelectedPresentationList
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param SummitSelectedPresentationList $list
     */
    public function setList($list)
    {
        $this->list = $list;
    }

    /**
     * @return Presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @param Presentation $presentation
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
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
}
