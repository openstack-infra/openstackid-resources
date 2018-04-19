<?php namespace models\summit;

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
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use models\main\Group;
use Doctrine\ORM\Mapping AS ORM;
/**
 * Class PrivatePresentationCategoryGroup
 * @ORM\Entity
 * @ORM\Table(name="PrivatePresentationCategoryGroup")
 * @package models\summit
 */
class PrivatePresentationCategoryGroup extends PresentationCategoryGroup
{
    /**
     * @ORM\Column(name="SubmissionBeginDate", type="datetime")
     * @var DateTime
     */
    protected $submission_begin_date;

    /**
     * @ORM\Column(name="SubmissionEndDate", type="datetime")
     * @var DateTime
     */
    protected $submission_end_date;

    /**
     * @ORM\Column(name="MaxSubmissionAllowedPerUser", type="integer")
     * @var int
     */
    protected $max_submission_allowed_per_user;

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Group")
     * @ORM\JoinTable(name="PrivatePresentationCategoryGroup_AllowedGroups",
     *      joinColumns={@ORM\JoinColumn(name="PrivatePresentationCategoryGroupID", referencedColumnName="ID", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="GroupID", referencedColumnName="ID", onDelete="CASCADE")}
     * )
     * @var Group[]
     */
    protected $allowed_groups;

    /**
     * @param Group $group
     */
    public function addToGroup(Group $group)
    {
        if ($this->allowed_groups->contains($group)) return;
        $this->allowed_groups->add($group);
    }

    /**
     * @param Group $group
     */
    public function removeFromGroup(Group $group)
    {
        if (!$this->allowed_groups->contains($group)) return;
        $this->allowed_groups->removeElement($group);
    }

    /**
     * @param int $group_id
     * @return Group|null
     */
    public function getGroupById($group_id)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($group_id)));
        $res = $this->allowed_groups->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @param int $group_id
     * @return bool
     */
    public function belongsToGroup($group_id)
    {
        return $this->getGroupById($group_id) != null;
    }

    /**
     * @return bool
     */
    public function isSubmissionOpen()
    {

        if (empty($this->submission_begin_date) || empty($this->submission_end_date)) {
            return false;
        }

        $start_date = new DateTime($this->submission_begin_date->getTimestamp(), new DateTimeZone('UTC'));
        $end_date = new DateTime($this->submission_end_date->getTimestamp(), new DateTimeZone('UTC'));
        $now = new DateTime('now', new DateTimeZone('UTC'));

        return ($now >= $start_date && $now <= $end_date);
    }

    public function __construct()
    {
        parent::__construct();
        $this->allowed_groups                  = new ArrayCollection;
        $this->max_submission_allowed_per_user = 0;
    }

    /**
     * @return DateTime
     */
    public function getSubmissionBeginDate()
    {
        return $this->submission_begin_date;
    }

    /**
     * @return DateTime
     */
    public function getSubmissionEndDate()
    {
        return $this->submission_end_date;
    }

    /**
     * @return int
     */
    public function getMaxSubmissionAllowedPerUser()
    {
        return $this->max_submission_allowed_per_user;
    }

    /**
     * @return Group[]
     */
    public function getAllowedGroups()
    {
        return $this->allowed_groups;
    }

    const ClassName = 'PrivatePresentationCategoryGroup';

    /**
     * @return string
     */
    public function getClassName()
    {
        return self::ClassName;
    }

    /**
     * @param DateTime $submission_begin_date
     */
    public function setSubmissionBeginDate(DateTime $submission_begin_date)
    {
        $summit = $this->getSummit();
        if (!is_null($summit)) {
            $submission_begin_date = $summit->convertDateFromTimeZone2UTC($submission_begin_date);
        }
        $this->submission_begin_date = $submission_begin_date;
    }

    /**
     * @param DateTime $submission_end_date
     */
    public function setSubmissionEndDate(DateTime $submission_end_date)
    {
        $summit = $this->getSummit();
        if (!is_null($summit)) {
            $submission_end_date = $summit->convertDateFromTimeZone2UTC($submission_end_date);
        }
        $this->submission_end_date = $submission_end_date;
    }

    /**
     * @param int $max_submission_allowed_per_user
     */
    public function setMaxSubmissionAllowedPerUser($max_submission_allowed_per_user)
    {
        $this->max_submission_allowed_per_user = $max_submission_allowed_per_user;
    }

    /**
     * @return DateTime|null
     */
    public function getLocalSubmissionBeginDate()
    {
        $res = null;
        if(!empty($this->submission_begin_date)) {
            $value  = clone $this->submission_begin_date;
            $summit = $this->getSummit();
            if(!is_null($summit))
            {
                $res = $summit->convertDateFromUTC2TimeZone($value);
            }
        }
        return $res;
    }

    /**
     * @return DateTime|null
     */
    public function getLocalSubmissionEndDate()
    {
        $res = null;
        if(!empty($this->submission_end_date)) {
            $value  = clone $this->submission_end_date;
            $summit = $this->getSummit();
            if(!is_null($summit))
            {
                $res = $summit->convertDateFromUTC2TimeZone($value);
            }
        }
        return $res;
    }


    public static $metadata = [
        'class_name'                      => self::ClassName,
        'submission_begin_date'           => 'datetime',
        'submission_end_date'             => 'datetime',
        'max_submission_allowed_per_user' => 'integer',
        'allowed_groups'                  => 'array'
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(PresentationCategoryGroup::getMetadata(), self::$metadata);
    }

}