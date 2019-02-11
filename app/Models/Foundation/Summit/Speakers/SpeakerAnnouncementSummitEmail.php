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
use models\summit\Speaker;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;
use DateTime;
/**
 * @ORM\Entity
 * @ORM\Table(name="SpeakerAnnouncementSummitEmail")
 * Class SpeakerAnnouncementSummitEmail
 * @package models\summit
 */
class SpeakerAnnouncementSummitEmail extends SilverstripeBaseModel
{

    const TypeAccepted                = 'ACCEPTED';
    const TypeRejected                = 'REJECTED';
    const TypeAlternate               = 'ALTERNATE';
    const TypeAcceptedAlternate       = 'ACCEPTED_ALTERNATE';
    const TypeAcceptedRejected        = 'ACCEPTED_REJECTED';
    const TypeAlternateRejected       = 'ALTERNATE_REJECTED';
    const TypeSecondBreakoutReminder  = 'SECOND_BREAKOUT_REMINDER';
    const TypeSecondBreakoutRegister  = 'SECOND_BREAKOUT_REGISTER';
    const TypeCreateMembership        = 'CREATE_MEMBERSHIP';
    const TypeNone                    = 'NONE';

    /**
     * @ORM\Column(name="AnnouncementEmailTypeSent", type="string")
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(name="AnnouncementEmailSentDate", type="datetime")
     * @var DateTime
     */
    private $send_date;

    Use SummitOwned;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\Speaker", inversedBy="announcement_summit_emails")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var Speaker
     */
    protected $speaker;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return DateTime
     */
    public function getSendDate()
    {
        return $this->send_date;
    }

    /**
     * @param DateTime $send_date
     */
    public function setSendDate(DateTime $send_date)
    {
        $this->send_date = $send_date;
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

}