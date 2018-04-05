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
use App\Models\Foundation\Summit\Events\RSVP\RSVPMultiValueQuestionTemplate;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Table(name="RSVPDropDownQuestionTemplate")
 * @ORM\Entity
 * Class RSVPDropDownQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPDropDownQuestionTemplate extends RSVPMultiValueQuestionTemplate
{
    const ClassName = 'RSVPDropDownQuestionTemplate';
    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    /**
     * @ORM\Column(name="IsMultiSelect", type="boolean")
     * @var bool
     */
    protected $is_multiselect;

    /**
     * @ORM\Column(name="IsCountrySelector", type="boolean")
     * @var bool
     */
    protected $is_country_selector;

    /**
     * @ORM\Column(name="UseChosenPlugin", type="boolean")
     * @var bool
     */
    protected $use_chosen_plugin;

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        return $this->is_multiselect;
    }

    /**
     * @param bool $is_multiselect
     */
    public function setIsMultiselect($is_multiselect)
    {
        $this->is_multiselect = $is_multiselect;
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

    /**
     * @return bool
     */
    public function getUseChosenPlugin()
    {
        return $this->use_chosen_plugin;
    }

    /**
     * @param bool $use_chosen_plugin
     */
    public function setUseChosenPlugin($use_chosen_plugin)
    {
        $this->use_chosen_plugin = $use_chosen_plugin;
    }

    public function __construct()
    {
        parent::__construct();
        $this->use_chosen_plugin   = false;
        $this->is_multiselect      = false;
        $this->is_country_selector = false;
    }


    public static $metadata = [
        'use_chosen_plugin'   => 'boolean',
        'is_multiselect'      => 'boolean',
        'is_country_selector' => 'boolean',
        'class_name'          => self::ClassName,
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(RSVPMultiValueQuestionTemplate::getMetadata(), self::$metadata);
    }
}