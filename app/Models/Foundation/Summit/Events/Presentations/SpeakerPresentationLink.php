<?php namespace models\summit;
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
 * @ORM\Table(name="SpeakerPresentationLink")
 * Class SpeakerPresentationLink
 * @package models\summit
 */
class SpeakerPresentationLink extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="LinkUrl", type="string")
     */
    private $link;

    /**
     * @ORM\Column(name="Title", type="string")
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="Speaker", inversedBy="other_presentation_links")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var Speaker
     */
    private $speaker;

    /**
     * SpeakerPresentationLink constructor.
     * @param string $link
     * @param string|null $title
     */
    public function __construct($link, $title = null)
    {
        parent::__construct();
        $this->link = $link;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return Speaker
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }

    /**
     * @param Speaker $speaker
     */
    public function setSpeaker($speaker)
    {
        $this->speaker = $speaker;
    }

    /**
     * @return int
     */
    public function getSpeakerId(){
        try {
            return !is_null($this->speaker) ? $this->speaker->getId() : 0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }


}