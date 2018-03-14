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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Table(name="RSVPMultiValueQuestionTemplate")
 * @ORM\Entity
 * Class RSVPMultiValueQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPMultiValueQuestionTemplate extends RSVPQuestionTemplate
{
    /**
     * @ORM\Column(name="EmptyString", type="string")
     * @var string
     */
    private $empty_string;

    /**
     * @ORM\OneToMany(targetEntity="RSVPQuestionValueTemplate", mappedBy="owner", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var RSVPQuestionValueTemplate[]
     */
    private $values;

    /**
     * @ORM\ManyToOne(targetEntity="RSVPQuestionValueTemplate", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="DefaultValueID", referencedColumnName="ID")
     * @var RSVPQuestionValueTemplate
     */
    private $default_value;

    /**
     * @return string
     */
    public function getEmptyString()
    {
        return $this->empty_string;
    }

    /**
     * @param string $empty_string
     */
    public function setEmptyString($empty_string)
    {
        $this->empty_string = $empty_string;
    }

    /**
     * @return RSVPQuestionValueTemplate[]
     */
    public function getValues()
    {
        $criteria = Criteria::create();
        $criteria->orderBy(['order'=> 'ASC']);
        return $this->values->matching($criteria);
    }

    /**
     * @param mixed $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return RSVPQuestionValueTemplate
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param RSVPQuestionValueTemplate $default_value
     */
    public function setDefaultValue(RSVPQuestionValueTemplate $default_value)
    {
        $this->default_value = $default_value;
    }

    public function __construct()
    {
        parent::__construct();
        $this->values = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return 'RSVPMultiValueQuestionTemplate';
    }

    /**
     * @return bool
     */
    public function hasDefaultValue(){
        return $this->getDefaultValueId() > 0;
    }

    /**
     * @return int
     */
    public function getDefaultValueId(){
        try{
            return is_null($this->default_value) ? 0 : $this->default_value->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

}