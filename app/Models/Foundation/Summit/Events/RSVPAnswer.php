<?php namespace models\summit;
/**
 * Copyright 2016 OpenStack Foundation
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

use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="RSVPAnswer")
 * Class RSVPAnswer
 * @package models\summit
 */
class RSVPAnswer extends SilverstripeBaseModel
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\RSVP", inversedBy="answers", fetch="LAZY")
     * @ORM\JoinColumn(name="RSVPID", referencedColumnName="ID")
     * @var SummitAttendee
     */
    private $rsvp;

    /**
     * @ORM\Column(name="Value", type="string")
     * @var string
     */
    private $value;

    /**
     * @return SummitAttendee
     */
    public function getRsvp()
    {
        return $this->rsvp;
    }

    /**
     * @param SummitAttendee $rsvp
     */
    public function setRsvp($rsvp)
    {
        $this->rsvp = $rsvp;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getQuestionId()
    {
        return $this->question_id;
    }

    /**
     * @param int $question_id
     */
    public function setQuestionId($question_id)
    {
        $this->question_id = $question_id;
    }

    /**
     * @ORM\Column(name="QuestionID", type="integer")
     * @var int
     */
    private $question_id;
}