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
     * Defines the phase that a presentation has been created, but
     * no information has been saved to it.
     */
    const PHASE_NEW = 0;

    /**
     * Defines the phase where a presenation has been given a summary,
     * but no speakers have been added
     */
    const PHASE_SUMMARY = 1;

    /**
     * defines a phase where a presentation has a tags
     */
    const PHASE_TAGS = 2;

    /**
     * defines a phase where a presentation has a summary and speakers
     */
    const PHASE_SPEAKERS = 3;


    /**
     * Defines a phase where a presentation has been submitted successfully
     */
    const PHASE_COMPLETE = 4;

    /**
     *
     */
    const STATUS_RECEIVED = 'Received';

    const ClassNamePresentation = 'Presentation';

    /**
     * @ORM\Column(name="Level", type="string")
     * @var string
     */
    private $level;

    /**
     * @ORM\Column(name="Status", type="string")
     * @var string
     */
    private $status;

    /**
     * @ORM\Column(name="Progress", type="integer")
     * @var int
     */
    private $progress;

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
     * @ORM\Column(name="ToRecord", type="boolean")
     * @var bool
     */
    protected $to_record;

    /**
     * @ORM\Column(name="FeatureCloud", type="boolean")
     * @var bool
     */
    protected $feature_cloud;

    /**
     * @ORM\ManyToOne(targetEntity="PresentationSpeaker", inversedBy="moderated_presentations")
     * @ORM\JoinColumn(name="ModeratorID", referencedColumnName="ID")
     * @var PresentationSpeaker
     */
    private $moderator;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\PresentationMaterial", mappedBy="presentation", cascade={"persist"}, orphanRemoval=true)
     * @var PresentationMaterial[]
     */
    private $materials;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationSpeaker", inversedBy="presentations")
     * @ORM\JoinTable(name="Presentation_Speakers",
     *  joinColumns={
     *     @ORM\JoinColumn(name="PresentationID", referencedColumnName="ID")
     * },
     * inverseJoinColumns={
     *      @ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID")
     *
     * }
     * )
     */
    private $speakers;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitSelectedPresentation", mappedBy="presentation", cascade={"persist"}, orphanRemoval=true)
     * @var SummitSelectedPresentation[]
     */
    private $selected_presentations;

    /**
     * @return bool
     */
    public function isToRecord()
    {
        return $this->to_record;
    }

    /**
     * @param bool $to_record
     */
    public function setToRecord($to_record)
    {
        $this->to_record = $to_record;
    }

    /**
     * @return boolean
     */
    public function getToRecord()
    {
        return $this->to_record;
    }

    public function __construct()
    {
        parent::__construct();
        $this->materials     = new ArrayCollection();
        $this->speakers      = new ArrayCollection();
        $this->to_record     = false;
        $this->feature_cloud = false;
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
        return self::ClassNamePresentation;
    }

    /**
     * @return PresentationSpeaker[]
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    /**
     * @param PresentationSpeaker $speaker
     */
    public function addSpeaker(PresentationSpeaker $speaker){
        $this->speakers->add($speaker);
        $speaker->addPresentation($this);
    }

    public function clearSpeakers(){
        $this->speakers->clear();
    }

    /**
     * @return int[]
     */
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
     * @param int $video_id
     * @return PresentationVideo
     */
    public function getVideoBy($video_id){
        return $this->materials
            ->filter(function( $element) use($video_id) { return $element instanceof PresentationVideo && $element->getId() == $video_id; })
            ->first();
    }

    /**
     * @param PresentationVideo $video
     */
    public function removeVideo(PresentationVideo $video){
        $this->materials->removeElement($video);
        $video->unsetPresentation();
    }

    /**
     * @param PresentationSpeaker $speaker
     */
    public function removeSpeaker(PresentationSpeaker $speaker){
        $this->speakers->removeElement($speaker);
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
    public function getModeratorId(){
        try {
            return !is_null($this->moderator)? $this->moderator->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return PresentationSpeaker
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * @param PresentationSpeaker $moderator
     */
    public function setModerator(PresentationSpeaker $moderator)
    {
        $this->moderator = $moderator;
    }

    public function unsetModerator(){
        $this->moderator = null;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * @param mixed $progress
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
    }

    /**
     * @return PresentationMaterial[]
     */
    public function getMaterials()
    {
        return $this->materials;
    }

    /**
     * @param PresentationMaterial[] $materials
     */
    public function setMaterials($materials)
    {
        $this->materials = $materials;
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

    /**
     * @return mixed
     */
    public function getFeatureCloud()
    {
        return $this->feature_cloud;
    }

    /**
     * @param mixed $feature_cloud
     */
    public function setFeatureCloud($feature_cloud)
    {
        $this->feature_cloud = $feature_cloud;
    }
}
