<?php namespace App\Models\Foundation\Summit\Events\Presentations;
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
 */
class PresentationSpeaker extends BaseEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\Speaker", inversedBy="presentations")
     * @ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID", onDelete="CASCADE")
     * @var Speaker
     */
    private $speaker;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\Presentation", inversedBy="speakers")
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
    public function getSpeaker(): Speaker
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
    public function getPresentation(): Presentation
    {
        return $this->presentation;
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
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }


}