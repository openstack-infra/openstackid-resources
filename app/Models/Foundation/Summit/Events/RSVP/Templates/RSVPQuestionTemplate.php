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
use Doctrine\ORM\Mapping AS ORM;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity
 * @ORM\Table(name="RSVPQuestionTemplate")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({
 *     "RSVPQuestionTemplate" = "RSVPQuestionTemplate",
 *     "RSVPLiteralContentQuestionTemplate"= "RSVPLiteralContentQuestionTemplate",
 *     "RSVPMultiValueQuestionTemplate" = "RSVPMultiValueQuestionTemplate",
 *     "RSVPSingleValueTemplateQuestion" = "RSVPSingleValueTemplateQuestion",
 *     "RSVPTextBoxQuestionTemplate" = "RSVPTextBoxQuestionTemplate",
 *     "RSVPTextAreaQuestionTemplate" = "RSVPTextAreaQuestionTemplate",
 *     "RSVPMemberEmailQuestionTemplate"     = "RSVPMemberEmailQuestionTemplate",
 *     "RSVPMemberFirstNameQuestionTemplate" = "RSVPMemberFirstNameQuestionTemplate",
 *     "RSVPMemberLastNameQuestionTemplate"  = "RSVPMemberLastNameQuestionTemplate",
 *     "RSVPCheckBoxListQuestionTemplate"    = "RSVPCheckBoxListQuestionTemplate",
 *     "RSVPRadioButtonListQuestionTemplate" = "RSVPRadioButtonListQuestionTemplate",
 *     "RSVPDropDownQuestionTemplate" = "RSVPDropDownQuestionTemplate"
 *     })
 * Class RSVPQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPQuestionTemplate extends SilverstripeBaseModel
{

    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="Label", type="string")
     * @var string
     */
    protected $label;

    /**
     * @ORM\Column(name="Mandatory", type="boolean")
     * @var boolean
     */
    protected $is_mandatory;

    /**
     * @ORM\Column(name="`Order`", type="string")
     * @var string
     */
    protected $order;

    /**
     * @ORM\Column(name="ReadOnly", type="boolean")
     * @var boolean
     */
    protected $is_read_only;

    /**
     * @ORM\ManyToOne(targetEntity="RSVPTemplate", fetch="EXTRA_LAZY", inversedBy="questions")
     * @ORM\JoinColumn(name="RSVPTemplateID", referencedColumnName="ID", onDelete="CASCADE")
     * @var RSVPTemplate
     */
    protected $template;

    /**
     * @ORM\OneToMany(targetEntity="RSVPQuestionDependsOn", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var RSVPQuestionDependsOn[]
     */
    protected $depends_on;

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
     * @return string
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param string $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
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
     * @return RSVPTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param RSVPTemplate $template
     */
    public function setTemplate(RSVPTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return 'RSVPQuestionTemplate';
    }

    public function clearTemplate(){
        $this->template = null;
    }

    public function __construct()
    {
        parent::__construct();
        $this->is_mandatory = false;
        $this->is_read_only = false;
        $this->order        = 0;
    }

}