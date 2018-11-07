<?php namespace App\Models\Foundation\Summit\Events\RSVP;
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
use App\Models\Foundation\Main\OrderableChilds;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use models\exceptions\ValidationException;
use models\main\Member;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Table(name="RSVPTemplate")
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineRSVPTemplateRepository")
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(
 *          name="summit",
 *          inversedBy="rsvp_templates"
 *     )
 * })
 * Class RSVPTemplate
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPTemplate extends SilverstripeBaseModel
{
    use SummitOwned;

    /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="Enabled", type="boolean")
     * @var bool
     */
    private $is_enabled;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", fetch="LAZY")
     * @ORM\JoinColumn(name="CreatedByID", referencedColumnName="ID")
     * @var Member
     */
    private $created_by;

    /**
     * @ORM\OneToMany(targetEntity="RSVPQuestionTemplate", mappedBy="template", cascade={"persist"}, orphanRemoval=true)
     * @var RSVPQuestionTemplate[]
     */
    private $questions;

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
     * @return bool
     */
    public function isEnabled()
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
     * @return Member
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param Member $created_by
     */
    public function setCreatedBy(Member $created_by)
    {
        $this->created_by = $created_by;
    }

    /**
     * @return int
     */
    public function getCreatedById(){
        try{
            return is_null($this->created_by) ? 0 : $this->created_by->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasCreatedBy(){
        return $this->getCreatedById() > 0;
    }

    public function __construct()
    {
        parent::__construct();
        $this->questions = new ArrayCollection;
    }

    /**
     * @return RSVPQuestionTemplate[]
     */
    public function getQuestions()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['order'=> 'ASC']);
        return $this->questions->matching($criteria);
    }

    /**
     * @param mixed $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @param  string $name
     * @return RSVPQuestionTemplate
     */
    public function getQuestionByName($name){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('name', trim($name)));
        $res = $this->questions->matching($criteria)->first();
        return $res ? $res : null;
    }

    /**
     * @param  int $id
     * @return RSVPQuestionTemplate
     */
    public function getQuestionById($id){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('id', intval($id)));
        $res = $this->questions->matching($criteria)->first();
        return $res ? $res : null;
    }

    /**
     * @param RSVPQuestionTemplate $question
     * @return $this
     */
    public function addQuestion(RSVPQuestionTemplate $question){
        $questions = $this->getQuestions();
        $this->questions->add($question);
        $question->setTemplate($this);
        $question->setOrder(count($questions) + 1);
        return $this;
    }

    use OrderableChilds;

    /**
     * @param RSVPQuestionTemplate $question
     * @param int $new_order
     * @throws ValidationException
     */
    public function recalculateQuestionOrder(RSVPQuestionTemplate $question, $new_order){
        self::recalculateOrderForSelectable($this->questions, $question, $new_order);
    }

    /**
     * @param RSVPQuestionTemplate $question
     * @return $this
     */
    public function removeQuestion(RSVPQuestionTemplate $question){
        $this->questions->removeElement($question);
        $question->clearTemplate();
        return $this;
    }
}