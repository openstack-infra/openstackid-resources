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

use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Presentation
 * @ORM\Entity
 * @ORM\Table(name="Presentation")
 * @package models\summit
 */
class Presentation extends SummitEvent
{

    /**
     * @var PresentationMaterial[]
     * @ORM\OneToMany(targetEntity="models\summit\PresentationMaterial", mappedBy="presentation")
     */
    private $materials;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationSpeaker", mappedBy="presentations")
     * @var PresentationSpeaker[]
     */
    private $speakers;

    public function __construct()
    {
        parent::__construct();
        $this->materials = new ArrayCollection();
        $this->speakers  = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getProblemAddressed()
    {
        return $this->problem_addressed;
    }

    /**
     * @param string $problem_addressed
     */
    public function setProblemAddressed($problem_addressed)
    {
        $this->problem_addressed = $problem_addressed;
    }

    /**
     * @ORM\Column(name="Level", type="string")
     * @var string
     */
    private $level;

    /**
     * @ORM\Column(name="ProblemAddressed", type="string")
     * @var string
     */
    private $problem_addressed;


    /**
     * @ORM\Column(name="AttendeesExpectedLearnt", type="string")
     * @var string
     */
    private $attendees_expected_learnt;

    /**
     * @return string
     */
    public function getAttendeesExpectedLearnt()
    {
        return $this->attendees_expected_learnt;
    }

    /**
     * @param string $attendees_expected_learnt
     */
    public function setAttendeesExpectedLearnt($attendees_expected_learnt)
    {
        $this->attendees_expected_learnt = $attendees_expected_learnt;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return "Presentation";
    }

    /**
     * @return PresentationSpeaker[]
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    public function getSpeakerIds()
    {
        return $this->speakers->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @return PresentationVideo[]
     */
    public function getVideos()
    {
        return $this->materials->filter(function( $element) { return $element instanceof PresentationVideo; });
    }

    /**
     * @param int $material_id
     * @return PresentationMaterial|null
     */
    public function getMaterial($material_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($material_id)));
        $material = $this->materials->matching($criteria)->first();
        return $material === false ? null:$material;
    }

    /**
     * @param PresentationVideo $video
     * @return $this
     */
    public function addVideo(PresentationVideo $video){
        $this->materials->add($video);
        $video->setPresentation($this);
    }

     /**
     * @return bool
     */
    public function hasVideos(){
        return count($this->getVideos()) > 0;
    }

    /**
     * @return PresentationSlide[]
     */
    public function getSlides()
    {
        return $this->materials->filter(function( $element) { return $element instanceof PresentationSlide; });
    }

    /**
     * @param PresentationSlide $slide
     * @return $this
     */
    public function addSlide(PresentationSlide $slide){
        $this->materials->add($slide);
        $slide->setPresentation($this);
    }

    /**
     * @return bool
     */
    public function hasSlides(){
        return count($this->getSlides()) > 0;
    }

    /**
     * @return PresentationLink[]
     */
    public function getLinks(){
        return $this->materials->filter(function($element) { return $element instanceof PresentationLink; });
    }

    /**
     * @return bool
     */
    public function hasLinks(){
        return count($this->getLinks()) > 0;
    }

    /**
     * @param PresentationLink $link
     * @return $this
     */
    public function addLink(PresentationLink $link){
        $this->materials->add($link);
        $link->setPresentation($this);
    }

    /**
     * @return int
     */
    public function getCategoryId(){
        try {
            return !is_null($this->category)? $this->category->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @ORM\ManyToOne(targetEntity="PresentationCategory")
     * @ORM\JoinColumn(name="CategoryID", referencedColumnName="ID")
     * @var PresentationCategory
     */
    private $category = null;

    /**
     * @param PresentationCategory $category
     * @return $this
     */
    public function setCategory(PresentationCategory $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return PresentationCategory
     */
    public function getCategory(){
        return $this->category;
    }


    /**
     * @return int
     */
    public function getModeratorId(){
        try {
            return !is_null($this->moderator)? $this->moderator->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @ORM\ManyToOne(targetEntity="PresentationSpeaker")
     * @ORM\JoinColumn(name="ModeratorID", referencedColumnName="ID")
     */
    private $moderator;

    /**
     * @return mixed
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * @param mixed $moderator
     */
    public function setModerator($moderator)
    {
        $this->moderator = $moderator;
    }


}
