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

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use models\main\Group;

/**
 * Class SummitGroupEvent
 * @ORM\Entity
 * @ORM\Table(name="SummitGroupEvent")
 * @package models\summit
 */
class SummitGroupEvent extends SummitEvent
{
    /**
     * @ORM\ManyToMany(targetEntity="models\main\Group")
     * @ORM\JoinTable(name="SummitGroupEvent_Groups",
     *  joinColumns={
     *      @ORM\JoinColumn(name="`SummitGroupEventID`", referencedColumnName="ID")
     * },
     * inverseJoinColumns={
     *      @ORM\JoinColumn(name="GroupID", referencedColumnName="ID")
     * }
     * )
     */
    private $groups;

    /**
     * @return string
     */
    public function getClassName(){
        return "SummitGroupEvent";
    }

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param ArrayCollection $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return int[]
     */
    public function getGroupsIds(){
        $ids = [];
        foreach ($this->getGroups() as $g){
            $ids[] = intval($g->getId());
        }
        return $ids;
    }

}