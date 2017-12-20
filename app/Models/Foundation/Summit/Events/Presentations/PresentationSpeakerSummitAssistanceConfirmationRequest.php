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
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * Class PresentationSpeakerSummitAssistanceConfirmationRequest
 * @ORM\Entity
 * @ORM\Table(name="PresentationSpeakerSummitAssistanceConfirmationRequest")
 * @package models\summit
 */
class PresentationSpeakerSummitAssistanceConfirmationRequest extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="OnSitePhoneNumber", type="string")
     * @var string
     */
    private $on_site_phone;

    /**
     * @ORM\Column(name="RegisteredForSummit", type="boolean")
     * @var bool
     */
    private $registered;

    /**
     * @ORM\Column(name="IsConfirmed", type="boolean")
     * @var bool
     */
    private $is_confirmed;

    /**
     * @ORM\Column(name="CheckedIn", type="boolean")
     * @var bool
     */
    private $checked_in;

    /**
     * @ORM\ManyToOne(targetEntity="PresentationSpeaker")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var PresentationSpeaker
     */
    private $speaker;

    /**
     * @return string
     */
    public function getOnSitePhone()
    {
        return $this->on_site_phone;
    }

    /**
     * @param string $on_site_phone
     */
    public function setOnSitePhone($on_site_phone)
    {
        $this->on_site_phone = $on_site_phone;
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        return $this->registered;
    }

    /**
     * @param bool $registered
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->is_confirmed;
    }

    /**
     * @param bool $is_confirmed
     */
    public function setIsConfirmed($is_confirmed)
    {
        $this->is_confirmed = $is_confirmed;
    }

    /**
     * @return bool
     */
    public function isCheckedIn()
    {
        return $this->checked_in;
    }

    /**
     * @param bool $checked_in
     */
    public function setCheckedIn($checked_in)
    {
        $this->checked_in = $checked_in;
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

    use SummitOwned;

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