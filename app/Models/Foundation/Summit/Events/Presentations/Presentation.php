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
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackAnswer;
use App\Models\Foundation\Summit\SelectionPlan;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use models\exceptions\ValidationException;
use models\main\Member;
/**
 * Class Presentation
 * @ORM\Entity
 * @ORM\Table(name="Presentation")
 * @package models\summit
 */
class Presentation extends SummitEvent
{
    /**
     * SELECTION STATUS (TRACK CHAIRS LIST)
     */
    const SelectionStatus_Accepted   = 'accepted';
    const SelectionStatus_Unaccepted = 'unaccepted';
    const SelectionStatus_Alternate  = 'alternate';

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

    const MaxAllowedLinks = 5;

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
     * @ORM\Column(name="AttendingMedia", type="boolean")
     * @var bool
     */
    protected $attending_media;

    /**
     * @ORM\ManyToOne(targetEntity="PresentationSpeaker", inversedBy="moderated_presentations")
     * @ORM\JoinColumn(name="ModeratorID", referencedColumnName="ID", onDelete="SET NULL")
     * @var PresentationSpeaker
     */
    private $moderator;


    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="CreatorID", referencedColumnName="ID", onDelete="SET NULL")
     * @var Member
     */
    private $creator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\Foundation\Summit\SelectionPlan", inversedBy="presentations")
     * @ORM\JoinColumn(name="SelectionPlanID", referencedColumnName="ID")
     * @var SelectionPlan
     */
    private $selection_plan;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\PresentationMaterial", mappedBy="presentation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var PresentationMaterial[]
     */
    private $materials;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationSpeaker", inversedBy="presentations")
     * @ORM\JoinTable(name="Presentation_Speakers",
     *  joinColumns={
     *     @ORM\JoinColumn(name="PresentationID", referencedColumnName="ID", onDelete="CASCADE")
     * },
     * inverseJoinColumns={
     *      @ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID",  onDelete="CASCADE")
     *
     * }
     * )
     */
    private $speakers;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitSelectedPresentation", mappedBy="presentation", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var SummitSelectedPresentation[]
     */
    private $selected_presentations;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackAnswer", mappedBy="presentation", cascade={"persist"}, orphanRemoval=true, fetch="EXTRA_LAZY")
     * @var TrackAnswer[]
     */
    private $answers;

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

        $this->materials       = new ArrayCollection();
        $this->speakers        = new ArrayCollection();
        $this->answers         = new ArrayCollection();
        $this->to_record       = false;
        $this->attending_media = false;
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
        if($this->speakers->contains($speaker)) return;
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
        $res = $this->materials
            ->filter(function( $element) use($video_id) { return $element instanceof PresentationVideo && $element->getId() == $video_id; })
            ->first();
        return $res === false ? null : $res;
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
        if(!$this->speakers->contains($speaker)) return;
        $this->speakers->removeElement($speaker);
    }

    /**
     * @param PresentationSpeaker $speaker
     * @return bool
     */
    public function isSpeaker(PresentationSpeaker $speaker){
        return $this->speakers->contains($speaker);
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
     * @return string
     */
    public function getStatusNice() {
        if ($this->isPublished())
            return 'Accepted';
        return $this->status;
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
     * @return bool
     */
    public function getAttendingMedia()
    {
        return $this->attending_media;
    }

    /**
     * @param bool $attending_media
     */
    public function setAttendingMedia($attending_media)
    {
        $this->attending_media = $attending_media;
    }

    /**
     * @return string
     * @throws ValidationException
     */
    public function getSelectionStatus()
    {

        $session_sel = $this->createQuery("SELECT sp from models\summit\SummitSelectedPresentation sp 
            JOIN sp.list l
            JOIN sp.presentation p
            WHERE p.id = :presentation_id 
            AND sp.collection = :collection
            AND l.list_type = :list_type
            AND l.list_class = :list_class")
            ->setParameter('presentation_id' , $this->id)
            ->setParameter('collection',  SummitSelectedPresentation::CollectionSelected)
            ->setParameter('list_type',  SummitSelectedPresentationList::Group)
            ->setParameter('list_class',  SummitSelectedPresentationList::Session)->getResult();

        // Error out if a talk has more than one selection
        if (count($session_sel) > 1) {
            throw new ValidationException('presentation has more than 1 (one) selection.');
        }

        $selection = null;
        if (count($session_sel) == 1) {
            $selection = $session_sel[0];
        }

        if (!$selection) {
            return Presentation::SelectionStatus_Unaccepted;
        }
        if ($selection->getOrder() <= $this->getCategory()->getSessionCount()) {
            return Presentation::SelectionStatus_Accepted;
        }

        return Presentation::SelectionStatus_Alternate;
    }

    /**
     * @return SelectionPlan
     */
    public function getSelectionPlan()
    {
        return $this->selection_plan;
    }

    /**
     * @param SelectionPlan $selection_plan
     */
    public function setSelectionPlan($selection_plan)
    {
        $this->selection_plan = $selection_plan;
    }

    public function clearSelectionPlan(){
        $this->selection_plan = null;
    }

    /**
     * @return Member
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param Member $creator
     */
    public function setCreator(Member $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return TrackAnswer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param TrackAnswer[] $answers
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    }

    /**
     * @param TrackAnswer $answer
     */
    public function addAnswer(TrackAnswer $answer){
        $this->answers->add($answer);
        $answer->setPresentation($this);
    }

    /**
     * @param string $link
     * @return PresentationLink|null
     */
    public function findLink($link){
        $links = $this->getLinks();

        foreach ($links as $entity){
           if($entity->getLink() == $link)
               return $entity;
        }
        return null;
    }

    public function clearLinks(){
        $links = $this->getLinks();

        foreach ($links as $link){
            $this->materials->removeElement($link);
            $link->clearPresentation();
        }
    }

    /**
     * @param TrackQuestionTemplate $question
     * @return TrackAnswer|null
     */
    public function getTrackExtraQuestionAnswer(TrackQuestionTemplate $question){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('question', $question));
        $res = $this->answers->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @return int
     */
    public function getCreatorId()
    {
        try{
            if(is_null($this->creator)) return 0;
            return $this->creator->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return int
     */
    public function getSelectionPlanId()
    {
        try{
            if(is_null($this->selection_plan)) return 0;
            return $this->selection_plan->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @param PresentationSpeaker $speaker
     * @return bool
     */
    public function canEdit(PresentationSpeaker $speaker){
        if($this->getCreatorId() == $speaker->getId()) return true;
        if($this->getModeratorId() == $speaker->getId()) return true;
        if($this->isSpeaker($speaker)) return true;
        return false;
    }

}
