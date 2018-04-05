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
/**
 * @ORM\Table(name="RSVPSingleValueTemplateQuestion")
 * @ORM\Entity
 * Class RSVPSingleValueTemplateQuestion
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPSingleValueTemplateQuestion extends RSVPQuestionTemplate
{
    /**
     * @ORM\Column(name="InitialValue", type="string")
     * @var string
     */
    protected $initial_value;

    /**
     * @return string
     */
    public function getInitialValue()
    {
        return $this->initial_value;
    }

    /**
     * @param string $initial_value
     */
    public function setInitialValue($initial_value)
    {
        $this->initial_value = $initial_value;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    const ClassName = 'RSVPSingleValueTemplateQuestion';

    public static $metadata = [
        'initial_value' => 'string',
        'class_name'    => self::ClassName,
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(RSVPQuestionTemplate::getMetadata(), self::$metadata);
    }
}