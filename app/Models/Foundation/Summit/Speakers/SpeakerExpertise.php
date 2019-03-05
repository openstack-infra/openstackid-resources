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
 * @ORM\Table(name="SpeakerExpertise")
 * Class SpeakerExpertise
 * @package models\summit
 */
class SpeakerExpertise extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Expertise", type="string")
     */
    private $expertise;

    /**
     * SpeakerExpertise constructor.
     * @param string $expertise
     */
    public function __construct($expertise)
    {
        parent::__construct();
        $this->expertise = $expertise;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Speaker", inversedBy="areas_of_expertise")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var Speaker
     */
    private $speaker;

    /**
     * @return string
     */
    public function getExpertise()
    {
        return $this->expertise;
    }

    /**
     * @param string $expertise
     */
    public function setExpertise($expertise)
    {
        $this->expertise = $expertise;
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