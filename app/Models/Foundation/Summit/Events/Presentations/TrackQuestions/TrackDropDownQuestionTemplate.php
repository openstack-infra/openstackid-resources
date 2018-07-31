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
/**
 * @ORM\Entity
 * @ORM\Table(name="TrackDropDownQuestionTemplate")
 * Class TrackDropDownQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\Presentations\TrackQuestions
 */
class TrackDropDownQuestionTemplate extends TrackMultiValueQuestionTemplate
{
    /**
     * @ORM\Column(name="IsMultiSelect", type="boolean")
     * @var bool
     */
    private $is_multi_select;

    /**
     * @ORM\Column(name="IsCountrySelector", type="boolean")
     * @var bool
     */
    private $is_country_selector;


    public function __construct()
    {
        parent::__construct();
        $this->is_multi_select = false;
        $this->is_country_selector = false;
    }

    /**
     * @return bool
     */
    public function isMultiSelect()
    {
        return $this->is_multi_select;
    }

    /**
     * @param bool $is_multi_select
     */
    public function setIsMultiSelect($is_multi_select)
    {
        $this->is_multi_select = $is_multi_select;
    }

    /**
     * @return bool
     */
    public function isCountrySelector()
    {
        return $this->is_country_selector;
    }

    /**
     * @param bool $is_country_selector
     */
    public function setIsCountrySelector($is_country_selector)
    {
        $this->is_country_selector = $is_country_selector;
    }


    const ClassName = 'TrackDropDownQuestionTemplate';

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public static $metadata = [
        'class_name'  => self::ClassName,
        'is_multi_select' => 'boolean',
        'is_country_selector' => 'boolean',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(TrackMultiValueQuestionTemplate::getMetadata(), self::$metadata);
    }
}