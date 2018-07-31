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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="TrackMultiValueQuestionTemplate")
 * Class TrackMultiValueQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\Presentations\TrackQuestions
 */
class TrackMultiValueQuestionTemplate extends TrackQuestionTemplate
{

    /**
     * @ORM\Column(name="EmptyString", type="string")
     * @var string
     */
    protected $empty_string;

    /**
     * @ORM\OneToMany(targetEntity="TrackQuestionValueTemplate", mappedBy="owner", cascade={"persist"})
     * @var TrackQuestionValueTemplate[]
     */
    protected $values;

    /**
     * @ORM\ManyToOne(targetEntity="TrackQuestionValueTemplate", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="DefaultValueID", referencedColumnName="ID", onDelete="SET NULL")
     * @var TrackQuestionValueTemplate
     */
    protected $default_value;

    public function __construct()
    {
        parent::__construct();
        $this->values = new ArrayCollection;
    }

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
     * @return TrackQuestionValueTemplate[]
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param TrackQuestionValueTemplate[] $values
     */
    public function setValues($values)
    {
        $this->values = $values;
    }

    /**
     * @return TrackQuestionValueTemplate
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param TrackQuestionValueTemplate $default_value
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    const ClassName = 'TrackMultiValueQuestionTemplate';

    public static $metadata = [
        'class_name'       => self::ClassName,
        'empty_string'     => 'string',
        'default_value_id' => 'int',
        'values'           => 'array'
    ];

    /**
     * @return int
     */
    public function getDefaultValueId(){
        try{
            if(is_null($this->default_value)) return 0;
            return $this->default_value->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(TrackQuestionTemplate::getMetadata(), self::$metadata);
    }

}