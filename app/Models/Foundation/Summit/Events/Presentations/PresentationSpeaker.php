<?php namespace models\summit;
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

use Doctrine\Common\Collections\Criteria;
use models\main\File;
use models\main\Member;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="PresentationSpeaker")
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSpeakerRepository")
 * Class PresentationSpeaker
 * @package models\summit
 */
class PresentationSpeaker extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="FirstName", type="string")
     */
    private $first_name;

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @param string $bio
     */
    public function setBio($bio)
    {
        $this->bio = $bio;
    }

    /**
     * @return string
     */
    public function getIrcHandle()
    {
        return $this->irc_handle;
    }

    /**
     * @param string $irc_handle
     */
    public function setIrcHandle($irc_handle)
    {
        $this->irc_handle = $irc_handle;
    }

    /**
     * @return string
     */
    public function getTwitterName()
    {
        return $this->twitter_name;
    }

    /**
     * @param string $twitter_name
     */
    public function setTwitterName($twitter_name)
    {
        $this->twitter_name = $twitter_name;
    }

    /**
     * @ORM\Column(name="LastName", type="string")
     */
    private $last_name;

    /**
     * @ORM\Column(name="Title", type="string")
     */
    private $title;

    /**
     * @ORM\Column(name="Bio", type="string")
     */
    private $bio;

    /**
     * @ORM\Column(name="IRCHandle", type="string")
     */
    private $irc_handle;

    /**
     * @ORM\Column(name="TwitterName", type="string")
     */
    private $twitter_name;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\Presentation", inversedBy="speakers")
     * @ORM\JoinTable(name="Presentation_Speakers",
     *  joinColumns={
     *      @ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID")
     * },
     * inverseJoinColumns={
     *      @ORM\JoinColumn(name="PresentationID", referencedColumnName="ID")
     * }
     * )
     */
    private $presentations;

    /**
     * @ORM\OneToMany(targetEntity="Presentation", mappedBy="moderator", cascade={"persist"})
     * @var Presentation[]
     */
    private $moderated_presentations;

    public function __construct()
    {
        parent::__construct();
        $this->presentations           = new ArrayCollection;
        $this->moderated_presentations = new ArrayCollection;
    }

    /**
     * @param Presentation $presentation
     */
    public function addPresentation(Presentation $presentation){
        $this->presentations->add($presentation);
    }

    /**
     * @param null|int $summit_id
     * @param bool|true $published_ones
     * @return Presentation[]
     */
    public function presentations($summit_id, $published_ones = true)
    {

        return $this->presentations
            ->filter(function($p) use($published_ones, $summit_id){
                $res = $published_ones? $p->isPublished(): true;
                $res &= is_null($summit_id)? true : $p->getSummit()->getId() == $summit_id;
                return $res;
            });
    }

    /**
     * @param null|int $summit_id
     * @param bool|true $published_ones
     * @return Presentation[]
     */
    public function moderated_presentations($summit_id, $published_ones = true)
    {

        return $this->moderated_presentations
            ->filter(function($p) use($published_ones, $summit_id){
                $res = $published_ones? $p->isPublished(): true;
                $res &= is_null($summit_id)? true : $p->getSummit()->getId() == $summit_id;
                return $res;
            });
    }

    /**
     * @param int $presentation_id
     * @return Presentation
     */
    public function getPresentation($presentation_id)
    {
        return $this->presentations->get($presentation_id);
    }

    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getPresentationIds($summit_id, $published_ones = true)
    {
        return $this->presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getPresentations($summit_id, $published_ones = true)
    {
        return $this->presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity;
        })->toArray();
    }


    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getModeratedPresentationIds($summit_id, $published_ones = true)
    {
        return $this->moderated_presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getModeratedPresentations($summit_id, $published_ones = true)
    {
        return $this->moderated_presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity;
        })->toArray();
    }


    /**
     * @ORM\ManyToOne(targetEntity="models\main\File")
     * @ORM\JoinColumn(name="PhotoID", referencedColumnName="ID")
     * @var File
     */
    protected $photo;

    /**
     * @return File
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member;

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @return bool
     */
    public function hasMember(){
        return $this->getMemberId() > 0;
    }

    /**
     * @return int
     */
    public function getMemberId()
    {
        try{
            return $this->member->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }
}