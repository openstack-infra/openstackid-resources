<?php namespace models\summit;
/**
 * Copyright 2019 OpenStack Foundation
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
use App\Models\Utils\BaseEntity;
use models\summit\Presentation;
use models\summit\Speaker;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="Presentation_Speakers")
 * Class PresentationSpeaker
 * @package models\summit
 */
class PresentationSpeaker extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="Speaker", inversedBy="presentations")
     * @ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID", onDelete="CASCADE")
     * @var Speaker
     */
    private $speaker;

    /**
     * @ORM\ManyToOne(targetEntity="Presentation", inversedBy="speakers")
     * @ORM\JoinColumn(name="PresentationID", referencedColumnName="ID", onDelete="CASCADE")
     * @var Presentation
     */
    private $presentation;

    /**
     * @ORM\Column(name="Role", type="string")
     * @var string
     */
    private $role;

    /**
     * @return Speaker
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }

    /**
     * @param Speaker $speaker
     * @return $this
     */
    public function setSpeaker(Speaker $speaker)
    {
        $this->speaker = $speaker;
        return $this;
    }

    /**
     * @return Presentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @return int
     */
    public function getPresentationId()
    {
        try {
            return !is_null($this->presentation) ? $this->presentation->getId() : 0;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    /**
     * @return int
     */
    public function getSpeakerId(){
        try {
            return !is_null($this->speaker) ? $this->speaker->getId() : 0;
        } catch (\Exception $ex) {
            return 0;
        }
    }

    /**
     * @param Presentation $presentation
     * @return $this
     */
    public function setPresentation(Presentation $presentation)
    {
        $this->presentation = $presentation;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role)
    {
        $this->role = $role;
    }
}
