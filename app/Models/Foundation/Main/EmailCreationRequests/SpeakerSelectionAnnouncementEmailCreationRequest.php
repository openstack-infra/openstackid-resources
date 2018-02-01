<?php namespace models\main;
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
use models\summit\PresentationSpeaker;
use models\summit\SpeakerAnnouncementSummitEmail;
use models\summit\SummitOwned;
use models\summit\SummitRegistrationPromoCode;

/**
 * @ORM\Entity
 * @ORM\Table(name="SpeakerSelectionAnnouncementEmailCreationRequest")
 * Class SpeakerSelectionAnnouncementEmailCreationRequest
 * @package models\main
 */
final class SpeakerSelectionAnnouncementEmailCreationRequest
    extends EmailCreationRequest
{

    use SummitOwned;

    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="SpeakerRole", type="string")
     * @var string
     */
    protected $speaker_role;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\PresentationSpeaker")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var PresentationSpeaker
     */
    protected $speaker;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitRegistrationPromoCode")
     * @ORM\JoinColumn(name="PromoCodeID", referencedColumnName="ID")
     * @var SummitRegistrationPromoCode
     */
    protected $promo_code;

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
     * @return string
     */
    public function getSpeakerRole()
    {
        return $this->speaker_role;
    }

    /**
     * @param string $speaker_role
     */
    public function setSpeakerRole($speaker_role)
    {
        $this->speaker_role = $speaker_role;
    }

    /**
     * @return PresentationSpeaker
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }

    /**
     * @param PresentationSpeaker $speaker
     */
    public function setSpeaker($speaker)
    {
        $this->speaker = $speaker;
    }

    /**
     * @return SummitRegistrationPromoCode
     */
    public function getPromoCode()
    {
        return $this->promo_code;
    }

    /**
     * @param SummitRegistrationPromoCode $promo_code
     */
    public function setPromoCode($promo_code)
    {
        $this->promo_code = $promo_code;
    }

    /**
     * @var array
     */
    public static $valid_types = [
        SpeakerAnnouncementSummitEmail::TypeAccepted,
        SpeakerAnnouncementSummitEmail::TypeAcceptedAlternate,
        SpeakerAnnouncementSummitEmail::TypeAcceptedRejected,
        SpeakerAnnouncementSummitEmail::TypeAlternate,
        SpeakerAnnouncementSummitEmail::TypeAlternateRejected,
    ];

    /**
     * @param string $type
     * @return bool
     */
    public static function isValidType($type){
        return in_array($type, self::$valid_types);
    }
}