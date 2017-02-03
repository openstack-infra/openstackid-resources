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
use Doctrine\ORM\Mapping as ORM;
use models\summit\Summit;
use models\summit\SummitEvent;
use models\summit\SummitEventFeedback;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="Member")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineMemberRepository")
 * Class Member
 * @package models\main
 */
class Member extends SilverstripeBaseModel
{
    public function __construct(){
        parent::__construct();
        $this->feedback     = new ArrayCollection();
        $this->groups       = new ArrayCollection();
        $this->affiliations = new ArrayCollection();
    }

    /**
     * @return Affiliation[]
     */
    public function getAffiliations(){
        return $this->affiliations;
    }

    /**
     * @return Group[]
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

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Group", inversedBy="members")
     * @ORM\JoinTable(name="Group_Members",
     *      joinColumns={@ORM\JoinColumn(name="MemberID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="GroupID", referencedColumnName="ID")}
     *      )
     * @var Group[]
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
     * @ORM\Column(name="Email", type="string")
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(name="SecondEmail", type="string")
     * @var string
     */
    private $second_email;

    /**
     * @ORM\Column(name="ThirdEmail", type="string")
     * @var string
     */
    private $third_email;

    /**
     * @ORM\Column(name="EmailVerified", type="boolean")
     * @var bool
     */
    private $email_verified;

    /**
     * @ORM\Column(name="EmailVerifiedDate", type="datetime")
     * @var \DateTime
     */
    private $email_verified_date;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isEmailVerified()
    {
        return $this->email_verified;
    }

    /**
     * @param bool $email_verified
     */
    public function setEmailVerified($email_verified)
    {
        $this->email_verified = $email_verified;
    }

    /**
     * @return \DateTime
     */
    public function getEmailVerifiedDate()
    {
        return $this->email_verified_date;
    }

    /**
     * @param \DateTime $email_verified_date
     */
    public function setEmailVerifiedDate($email_verified_date)
    {
        $this->email_verified_date = $email_verified_date;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @ORM\Column(name="Active", type="boolean")
     * @var bool
     */
    private $active;

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
     * @ORM\OneToMany(targetEntity="Affiliation", mappedBy="owner", cascade={"persist"})
     */
    private $affiliations;

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

    /**
     * @param SummitEvent $event
     * @return SummitEventFeedback[]
     */
    public function getFeedbackByEvent(SummitEvent $event){
        return $this->createQueryBuilder()
            ->select('distinct f')
            ->from('models\summit\SummitEventFeedback','f')
            ->join('f.event','e')
            ->join('f.owner','o')
            ->join('e.summit','s')
            ->where('e.id = :event_id and o.id = :owner_id')
            ->setParameter('event_id', $event->getId())
            ->setParameter('owner_id', $this->getId())
            ->getQuery()->getResult();
    }

    /**
     * @return bool
     */
    public function isAdmin(){

        $admin_group = $this->groups->filter(function($entity){
            return $entity->getCode() == Group::AdminGroupCode;
        });

        return !is_null($admin_group) && $admin_group != false && $admin_group->count() > 0;
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

    /**
     * @return string[]
     */
    public function getGroupsCodes(){
        $codes = [];
        foreach ($this->getGroups() as $g){
            $codes[] = $g->getCode();
        }
        return $codes;
    }
}