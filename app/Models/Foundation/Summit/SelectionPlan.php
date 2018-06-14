<?php namespace App\Models\Foundation\Summit;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Models\Utils\TimeZoneEntity;
use Doctrine\Common\Collections\ArrayCollection;
use models\summit\Presentation;
use models\summit\PresentationCategoryGroup;
use models\summit\Summit;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;
use DateTime;
/**
 * @ORM\Entity
 * @ORM\Table(name="SelectionPlan")
 * Class SelectionPlan
 * @package App\Models\Foundation\Summit
 */
class SelectionPlan extends SilverstripeBaseModel
{
    use SummitOwned;

    use TimeZoneEntity;

    const STATUS_SUBMISSION = 'SUBMISSION';
    const STATUS_SELECTION  = 'SELECTION';
    const STATUS_VOTING     = 'VOTING';

    /**
     * @ORM\Column(name="Name", type="string")
     * @var String
     */
    private $name;

    /**
     * @ORM\Column(name="MaxSubmissionAllowedPerUser", type="integer")
     * @var int
     */
    private $max_submission_allowed_per_user;

    /**
     * @ORM\Column(name="Enabled", type="boolean")
     * @var bool
     */
    private $is_enabled;

    /**
     * @ORM\Column(name="SubmissionBeginDate", type="datetime")
     * @var \DateTime
     */
    private $submission_begin_date;

    /**
     * @ORM\Column(name="SubmissionEndDate", type="datetime")
     * @var \DateTime
     */
    private $submission_end_date;

    /**
     * @ORM\Column(name="VotingBeginDate", type="datetime")
     * @var \DateTime
     */
    private $voting_begin_date;

    /**
     * @ORM\Column(name="VotingEndDate", type="datetime")
     * @var \DateTime
     */
    private $voting_end_date;

    /**
     * @ORM\Column(name="SelectionBeginDate", type="datetime")
     * @var \DateTime
     */
    private $selection_begin_date;

    /**
     * @ORM\Column(name="SelectionEndDate", type="datetime")
     * @var \DateTime
     */
    private $selection_end_date;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategoryGroup")
     * @ORM\JoinTable(name="SelectionPlan_CategoryGroups",
     *      joinColumns={@ORM\JoinColumn(name="SelectionPlanID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationCategoryGroupID", referencedColumnName="ID")}
     *      )
     * @var PresentationCategoryGroup[]
     */
    private $category_groups;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\Presentation", mappedBy="selection_plan", cascade={"persist"})
     * @var Presentation[]
     */
    private $presentations;

    /**
     * @return string
     */
    public function getTimeZoneId()
    {
        return $this->summit->getTimeZoneId();
    }

    /**
     * @return DateTime
     */
    public function getSubmissionBeginDate()
    {
        return $this->submission_begin_date;
    }

    /**
     * @param DateTime $submission_begin_date
     */
    public function setSubmissionBeginDate(DateTime $submission_begin_date){
        $this->submission_begin_date = $this->convertDateFromTimeZone2UTC($submission_begin_date);
    }

    /**
     * @return $this
     */
    public function clearSubmissionDates(){
        $this->submission_begin_date =  $this->submission_end_date = null;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getSubmissionEndDate()
    {
        return $this->submission_end_date;
    }

    /**
     * @param DateTime $submission_end_date
     */
    public function setSubmissionEndDate(DateTime $submission_end_date){
        $this->submission_end_date = $this->convertDateFromTimeZone2UTC($submission_end_date);
    }

    /**
     * @return DateTime
     */
    public function getVotingBeginDate()
    {
        return $this->voting_begin_date;
    }

    /**
     * @param DateTime $voting_begin_date
     */
    public function setVotingBeginDate(DateTime $voting_begin_date){
        $this->voting_begin_date = $this->convertDateFromTimeZone2UTC($voting_begin_date);
    }

    /**
     * @return $this
     */
    public function clearVotingDates(){
        $this->voting_begin_date = $this->voting_end_date = null;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getVotingEndDate()
    {
        return $this->voting_end_date;
    }

    /**
     * @param DateTime $voting_end_date
     */
    public function setVotingEndDate(DateTime $voting_end_date){
        $this->voting_end_date = $this->convertDateFromTimeZone2UTC($voting_end_date);
    }

    /**
     * @return DateTime
     */
    public function getSelectionBeginDate()
    {
        return $this->selection_begin_date;
    }

    /**
     * @param DateTime $selection_begin_date
     */
    public function setSelectionBeginDate(DateTime $selection_begin_date){
        $this->selection_begin_date = $this->convertDateFromTimeZone2UTC($selection_begin_date);
    }

    /**
     * @return $this
     */
    public function clearSelectionDates(){
        $this->selection_begin_date =  $this->selection_end_date = null;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getSelectionEndDate()
    {
        return $this->selection_end_date;
    }

    /**
     * @param DateTime $selection_end_date
     */
    public function setSelectionEndDate(DateTime $selection_end_date){
        $this->selection_end_date = $this->convertDateFromTimeZone2UTC($selection_end_date);
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
     * @return bool
     */
    public function IsEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     */
    public function setIsEnabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;
    }

    /**
     * SelectionPlan constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->is_enabled                      = false;
        $this->category_groups                 = new ArrayCollection;
        $this->presentations                   = new ArrayCollection;
        $this->max_submission_allowed_per_user = Summit::DefaultMaxSubmissionAllowedPerUser;
    }

    /**
     * @return PresentationCategoryGroup[]
     */
    public function getCategoryGroups()
    {
        return $this->category_groups;
    }

    /**
     * @param PresentationCategoryGroup $track_group
     */
    public function addTrackGroup(PresentationCategoryGroup $track_group){
        if($this->category_groups->contains($track_group)) return;
        $this->category_groups->add($track_group);
    }

    /**
     * @param PresentationCategoryGroup $track_group
     */
    public function removeTrackGroup(PresentationCategoryGroup $track_group){
        if(!$this->category_groups->contains($track_group)) return;
        $this->category_groups->removeElement($track_group);
    }

    /**
     * @return int
     */
    public function getMaxSubmissionAllowedPerUser()
    {
        return $this->max_submission_allowed_per_user;
    }

    /**
     * @param int $max_submission_allowed_per_user
     */
    public function setMaxSubmissionAllowedPerUser($max_submission_allowed_per_user)
    {
        $this->max_submission_allowed_per_user = $max_submission_allowed_per_user;
    }

    /**
     * @return Presentation[]
     */
    public function getPresentations()
    {
        return $this->presentations;
    }

    /**
     * @param Presentation $presentation
     */
    public function addPresentation(Presentation $presentation){
        if($this->presentations->contains($presentation)) return;
        $this->presentations->add($presentation);
        $presentation->setSelectedPresentations($this);
    }
}