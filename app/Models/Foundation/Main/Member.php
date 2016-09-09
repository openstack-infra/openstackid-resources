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
use models\summit\Summit;
use models\summit\SummitEventFeedback;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Member")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineMemberRepository")
 * Class Member
 * @package models\main
 */
class Member extends SilverstripeBaseModel
{
    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    public function __construct(){
        parent::__construct();
        $this->feedback = new ArrayCollection();
        $this->groups   = new ArrayCollection();
    }

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Group", inversedBy="members")
     * @ORM\JoinTable(name="Group_Members",
     *      joinColumns={@ORM\JoinColumn(name="MemberID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="GroupID", referencedColumnName="ID")}
     *      )
     */
    private $groups;

    /**
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @return string
     */
    public function getLinkedInProfile()
    {
        return $this->linked_in_profile;
    }

    /**
     * @return string
     */
    public function getIrcHandle()
    {
        return $this->irc_handle;
    }

    /**
     * @return string
     */
    public function getTwitterHandle()
    {
        return $this->twitter_handle;
    }
    /**
     * @ORM\ManyToOne(targetEntity="models\main\File")
     * @ORM\JoinColumn(name="PhotoID", referencedColumnName="ID")
     * @var File
     */
    private $photo;

    /**
     * @ORM\Column(name="FirstName", type="string")
     * @var string
     */
    private $first_name;

    /**
     * @ORM\Column(name="Bio", type="string")
     * @var string
     */
    private $bio;

    /**
     * @ORM\Column(name="LinkedInProfile", type="string")
     * @var string
     */
    private $linked_in_profile;

    /**
     * @ORM\Column(name="IRCHandle", type="string")
     * @var string
     */
    private $irc_handle;

    /**
     * @ORM\Column(name="TwitterName", type="string")
     * @var string
     */
    private $twitter_handle;


    /**
     * @ORM\Column(name="Gender", type="string")
     * @var string
     */
    private $gender;

    /**
     * @return string
     */
    public function getGender(){
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @ORM\Column(name="Surname", type="string")
     * @var string
     */
    private $last_name;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitEventFeedback", mappedBy="owner", cascade={"persist"})
     * @var SummitEventFeedback[]
     */
    private $feedback;

    /**
     * @return File
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param File $photo
     */
    public function setPhoto(File $photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return SummitEventFeedback[]
     */
    public function getFeedback(){
        return $this->feedback;
    }

    /**
     * @param Summit $summit
     * @return SummitEventFeedback[]
     */
    public function getFeedbackBySummit(Summit $summit){
         return $this->createQueryBuilder()
            ->select('distinct f')
            ->from('models\summit\SummitEventFeedback','f')
            ->join('f.event','e')
             ->join('f.owner','o')
            ->join('e.summit','s')
            ->where('s.id = :summit_id and o.id = :owner_id')
            ->setParameter('summit_id', $summit->getId())
            ->setParameter('owner_id', $this->getId())
            ->getQuery()->getResult();
    }
}