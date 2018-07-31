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
 * @ORM\Table(name="TrackLiteralContentQuestionTemplate")
 * Class TrackLiteralContentQuestionTemplate
 * @package App\Models\Foundation\Summit\Events\Presentations\TrackQuestions
 */
class TrackLiteralContentQuestionTemplate extends TrackQuestionTemplate
{

    /**
     * @ORM\Column(name="Content", type="string")
     * @var string
     */
    private $content;

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    const ClassName = 'TrackLiteralContentQuestionTemplate';

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public static $metadata = [
        'class_name' => self::ClassName,
        'content' => 'string',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(TrackQuestionTemplate::getMetadata(), self::$metadata);
    }
}