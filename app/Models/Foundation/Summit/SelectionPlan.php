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
use App\Models\Utils\TimeZoneEntity;
use Doctrine\Common\Collections\ArrayCollection;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
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

    /**
     * @return string
     */
    public function getTimeZoneId()
    {
        return $this->summit->getTimeZoneId();
    }

    /**
     * @ORM\Column(name="Name", type="string")
     * @var String
     */
    private $name;

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

    /*
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategoryGroup")
     * @ORM\JoinTable(name="SelectionPlan_CategoryGroups",
     *      joinColumns={@JoinColumn(name="SelectionPlanID", referencedColumnName="ID")},
     *      inverseJoinColumns={@JoinColumn(name="PresentationCategoryGroupID", referencedColumnName="ID")}
     *      )
     * @var PresentationCategoryGroup[]
     */
    private $category_groups;

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

    public function __construct()
    {
        parent::__construct();
        $this->is_enabled = false;
        $this->category_groups = new ArrayCollection;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategoryGroups()
    {
        return $this->category_groups;
    }

}