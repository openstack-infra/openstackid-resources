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
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Table(name="RSVPQuestionTemplate_DependsOn")
 * @ORM\Entity
 * Class RSVPQuestionDependsOn
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPQuestionDependsOn extends SilverstripeBaseModel
{
    /**
     * @ORM\ManyToOne(targetEntity="RSVPQuestionTemplate", inversedBy="depends_on")
     * @ORM\JoinColumn(name="RSVPQuestionTemplateID", referencedColumnName="ID", onDelete="CASCADE")
     * @var RSVPQuestionTemplate
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="RSVPQuestionTemplate")
     * @ORM\JoinColumn(name="ChildID", referencedColumnName="ID", onDelete="CASCADE")
     * @var RSVPQuestionTemplate
     */
    private $child;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var string
     */
    private $visibility;

    /**
     * @var string
     */
    private $default_value;

    /**
     * @var string
     */
    private $boolean_op;

    /**
     * @return RSVPQuestionTemplate
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param RSVPQuestionTemplate $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return RSVPQuestionTemplate
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * @param RSVPQuestionTemplate $child
     */
    public function setChild($child)
    {
        $this->child = $child;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param string $default_value
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;
    }

    /**
     * @return string
     */
    public function getBooleanOp()
    {
        return $this->boolean_op;
    }

    /**
     * @param string $boolean_op
     */
    public function setBooleanOp($boolean_op)
    {
        $this->boolean_op = $boolean_op;
    }
}