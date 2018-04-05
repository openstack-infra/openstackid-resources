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
 * @ORM\Table(name="RSVPCheckBoxListQuestionTemplate")
 * @ORM\Entity
 * Class RSVPCheckBoxListQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPCheckBoxListQuestionTemplate extends RSVPMultiValueQuestionTemplate
{
    const ClassName = 'RSVPCheckBoxListQuestionTemplate';
    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public static $metadata = [
        'class_name'  => self::ClassName,
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(RSVPMultiValueQuestionTemplate::getMetadata(), self::$metadata);
    }
}