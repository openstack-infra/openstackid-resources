<?php namespace App\Models\Foundation\Summit\Events\Presentations\TrackQuestions;
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
use Doctrine\Common\Collections\ArrayCollection;
use models\summit\PresentationCategory;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity
 * @ORM\Table(name="TrackQuestionTemplate")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({
 *     "TrackQuestionTemplate" = "TrackQuestionTemplate",
 *     "TrackSingleValueTemplateQuestion" = "TrackSingleValueTemplateQuestion",
 *     "TrackMultiValueQuestionTemplate" = "TrackMultiValueQuestionTemplate",
 *     "TrackLiteralContentQuestionTemplate" = "TrackLiteralContentQuestionTemplate",
 *     "TrackRadioButtonListQuestionTemplate" = "TrackRadioButtonListQuestionTemplate",
 *     "TrackCheckBoxListQuestionTemplate" = "TrackCheckBoxListQuestionTemplate",
 *     "TrackDropDownQuestionTemplate" = "TrackDropDownQuestionTemplate",
 *     "TrackTextBoxQuestionTemplate" = "TrackTextBoxQuestionTemplate",
 *     "TrackCheckBoxQuestionTemplate" = "TrackCheckBoxQuestionTemplate"
 *     })
 * Class TrackQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\Presentations
 */
class TrackQuestionTemplate extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="label", type="string")
     * @var string
     */
    protected $label;

    /**
     * @ORM\Column(name="Mandatory", type="boolean")
     * @var bool
     */
    protected $is_mandatory;

    /**
     * @ORM\Column(name="ReadOnly", type="boolean")
     * @var bool
     */
    protected $is_read_only;

    /**
     * @ORM\Column(name="AfterQuestion", type="string")
     * @var string
     */
    protected $after_question;


    /**
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationCategory", mappedBy="extra_questions")
     * @var PresentationCategory[]
     */
    protected $tracks;

    /**
     * @ORM\OneToMany(targetEntity="TrackAnswer", mappedBy="question", cascade={"persist"})
     * @var TrackAnswer[]
     */
    protected $answers;

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    const ClassName = 'TrackQuestionTemplate';

    public function __construct()
    {
        parent::__construct();
        $this->is_mandatory = false;
        $this->is_read_only = false;
        $this->tracks = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

    public static $metadata = [
        'name'           => 'string',
        'label'          => 'string',
        'is_mandatory'   => 'boolean',
        'is_read_only'   => 'boolean',
        'after_question' => 'string',
        'tracks'         => 'array',
        'answers'        => 'array',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return self::$metadata;
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
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return bool
     */
    public function isMandatory()
    {
        return $this->is_mandatory;
    }

    /**
     * @param bool $is_mandatory
     */
    public function setIsMandatory($is_mandatory)
    {
        $this->is_mandatory = $is_mandatory;
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        return $this->is_read_only;
    }

    /**
     * @param bool $is_read_only
     */
    public function setIsReadOnly($is_read_only)
    {
        $this->is_read_only = $is_read_only;
    }

    /**
     * @return string
     */
    public function getAfterQuestion()
    {
        return $this->after_question;
    }

    /**
     * @param string $after_question
     */
    public function setAfterQuestion($after_question)
    {
        $this->after_question = $after_question;
    }

    /**
     * @return PresentationCategory[]
     */
    public function getTracks()
    {
        return $this->tracks;
    }

    /**
     * @param PresentationCategory[] $tracks
     */
    public function setTracks($tracks)
    {
        $this->tracks = $tracks;
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
        $answer->setQuestion($this);
    }
}