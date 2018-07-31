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
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate;
use Doctrine\Common\Collections\Criteria;
use models\main\Tag;
use models\utils\SilverstripeBaseModel;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Class PresentationCategory
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitTrackRepository")
 * @ORM\Table(name="PresentationCategory")
 * @package models\summit
 */
class PresentationCategory extends SilverstripeBaseModel
{

    use SummitOwned;

    /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="Code", type="string")
     * @var string
     */
    private $code;

    /**
     * @ORM\Column(name="Slug", type="string")
     * @var string
     */
    private $slug;

    /**
     * @ORM\Column(name="SessionCount", type="integer")
     * @var int
     */
    private $session_count;

    /**
     * @ORM\Column(name="AlternateCount", type="integer")
     * @var int
     */
    private $alternate_count;

    /**
     * @ORM\Column(name="LightningCount", type="integer")
     * @var int
     */
    private $lightning_count;

    /**
     * @ORM\Column(name="LightningAlternateCount", type="integer")
     * @var int
     */
    private $lightning_alternate_count;

    /**
     * @ORM\Column(name="VotingVisible", type="boolean")
     * @var boolean
     */
    private $voting_visible;

    /**
     * @ORM\Column(name="ChairVisible", type="boolean")
     * @var boolean
     */
    private $chair_visible;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     *
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategoryGroup", mappedBy="categories")
     * @var PresentationCategoryGroup[]
     */
    private $groups;

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Tag", cascade={"persist"})
     * @ORM\JoinTable(name="PresentationCategory_AllowedTags",
     *      joinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="TagID", referencedColumnName="ID")}
     *      )
     */
    protected $allowed_tags;


    /**
     * @ORM\ManyToMany(targetEntity="App\Models\Foundation\Summit\Events\Presentations\TrackQuestions\TrackQuestionTemplate", cascade={"persist"}, inversedBy="tracks")
     * @ORM\JoinTable(name="PresentationCategory_ExtraQuestions",
     *      joinColumns={@ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="TrackQuestionTemplateID", referencedColumnName="ID")}
     *      )
     * @var TrackQuestionTemplate[]
     */
    protected $extra_questions;


    /**
     * @param int $id
     * @return TrackQuestionTemplate|null
     */
    public function getExtraQuestionById($id){
        $res = $this->extra_questions->filter(function(TrackQuestionTemplate $question) use($id){
           return $question->getIdentifier() == $id;
        });
        $res = $res->first();
        return $res === false ? null : $res;
    }

    /**
     * @param string $name
     * @return TrackQuestionTemplate|null
     */
    public function getExtraQuestionByName($name){
        $res = $this->extra_questions->filter(function(TrackQuestionTemplate $question) use($name){
            return $question->getName() == trim($name);
        });
        $res = $res->first();
        return $res === false ? null : $res;
    }

    public function __construct()
    {
        parent::__construct();

        $this->groups                    = new ArrayCollection;
        $this->allowed_tags              = new ArrayCollection;
        $this->extra_questions           = new ArrayCollection;
        $this->session_count             = 0;
        $this->alternate_count           = 0;
        $this->lightning_alternate_count = 0;
        $this->lightning_count           = 0;
        $this->chair_visible             = false;
        $this->voting_visible            = false;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return PresentationCategoryGroup[]
     */
    public function getGroups(){
        return $this->groups;
    }

    /**
     * @param PresentationCategoryGroup $group
     */
    public function addToGroup(PresentationCategoryGroup $group){
        $this->groups->add($group);
    }

    /**
     * @param PresentationCategoryGroup $group
     */
    public function removeFromGroup(PresentationCategoryGroup $group){
        $this->groups->removeElement($group);
    }

    /**
     * @return Tag[]
     */
    public function getAllowedTags(){
        return $this->allowed_tags;
    }

    /**
     * @param int $group_id
     * @return PresentationCategoryGroup|null
     */
    public function getGroupById($group_id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($group_id)));
        $res = $this->groups->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @param int $group_id
     * @return bool
     */
    public function belongsToGroup($group_id){
        return $this->getGroupById($group_id) != null;
    }

    /**
     * @return int
     */
    public function getSessionCount()
    {
        return $this->session_count;
    }

    /**
     * @param int $session_count
     */
    public function setSessionCount($session_count)
    {
        $this->session_count = $session_count;
    }

    /**
     * @return int
     */
    public function getAlternateCount()
    {
        return $this->alternate_count;
    }

    /**
     * @param int $alternate_count
     */
    public function setAlternateCount($alternate_count)
    {
        $this->alternate_count = $alternate_count;
    }

    /**
     * @return int
     */
    public function getLightningCount()
    {
        return $this->lightning_count;
    }

    /**
     * @param int $lightning_count
     */
    public function setLightningCount($lightning_count)
    {
        $this->lightning_count = $lightning_count;
    }

    /**
     * @return int
     */
    public function getLightningAlternateCount()
    {
        return $this->lightning_alternate_count;
    }

    /**
     * @param int $lightning_alternate_count
     */
    public function setLightningAlternateCount($lightning_alternate_count)
    {
        $this->lightning_alternate_count = $lightning_alternate_count;
    }

    /**
     * @return bool
     */
    public function isVotingVisible()
    {
        return $this->voting_visible;
    }

    /**
     * @param bool $voting_visible
     */
    public function setVotingVisible($voting_visible)
    {
        $this->voting_visible = $voting_visible;
    }

    /**
     * @return bool
     */
    public function isChairVisible()
    {
        return $this->chair_visible;
    }

    /**
     * @param bool $chair_visible
     */
    public function setChairVisible($chair_visible)
    {
        $this->chair_visible = $chair_visible;
    }

    /**
     * @return $this
     */
    public function calculateSlug(){
        if(empty($this->title)) return $this;
        $clean_title = preg_replace ("/[^a-zA-Z0-9 ]/", "", $this->title);
        $this->slug = preg_replace('/\s+/', '-', strtolower($clean_title));
        return $this;
    }

    /**
     * @return SummitEvent[]
     */
    public function getRelatedPublishedSummitEvents(){
        $query = <<<SQL
SELECT e  
FROM  models\summit\SummitEvent e
WHERE 
e.published = 1
AND e.summit = :summit
AND e.category = :track
SQL;

        $native_query = $this->getEM()->createQuery($query);

        $native_query->setParameter("summit", $this->summit);
        $native_query->setParameter("track", $this);

        $res =  $native_query->getResult();

        return $res;
    }

    /**
     * @return int[]
     */
    public function getRelatedPublishedSummitEventsIds(){
        $query = <<<SQL
SELECT e.id  
FROM  models\summit\SummitEvent e
WHERE 
e.published = 1
AND e.summit = :summit
AND e.category = :track
SQL;

        $native_query = $this->getEM()->createQuery($query);

        $native_query->setParameter("summit", $this->summit);
        $native_query->setParameter("track", $this);

        $res =  $native_query->getResult();

        return $res;
    }
}