<?php namespace models\summit;
/**
 * Copyright 2017 OpenStack Foundation
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
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSpeakerSummitRegistrationPromoCodeRepository")
 * @ORM\Table(name="SpeakerSummitRegistrationPromoCode")
 * Class SpeakerSummitRegistrationPromoCode
 * @package models\summit
 */
class SpeakerSummitRegistrationPromoCode extends SummitRegistrationPromoCode
{
    const TypeAccepted  = 'ACCEPTED';
    const TypeAlternate = 'ALTERNATE';

    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Speaker", inversedBy="promo_codes")
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

    public function __construct()
    {
        parent::__construct();
        $this->redeemed = false;
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

    /**
     * @return bool
     */
    public function hasSpeaker(){
        return $this->getSpeakerId() > 0;
    }

    const ClassName = 'SPEAKER_PROMO_CODE';

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public static $metadata = [
        'class_name' => self::ClassName,
        'type'       => ["ACCEPTED", "ALTERNATE"],
        'speaker_id' => 'integer'
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(SummitRegistrationPromoCode::getMetadata(), self::$metadata);
    }

    public static $valid_type_values = ["ACCEPTED", "ALTERNATE"];
}