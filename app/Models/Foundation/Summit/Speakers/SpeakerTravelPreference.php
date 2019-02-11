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
use App\Models\Foundation\Main\CountryCodes;
/**
 * @ORM\Entity
 * @ORM\Table(name="SpeakerTravelPreference")
 * Class SpeakerTravelPreference
 * @package models\summit
 */
class SpeakerTravelPreference extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Country", type="string")
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="Speaker", inversedBy="travel_preferences")
     * @ORM\JoinColumn(name="SpeakerID", referencedColumnName="ID")
     * @var Speaker
     */
    private $speaker;

    /**
     * SpeakerTravelPreference constructor.
     * @param string $country
     */
    public function __construct($country)
    {
        parent::__construct();
        $this->country = $country;
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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCountryName(){
        if(isset(CountryCodes::$iso_3166_countryCodes[$this->country]))
            return CountryCodes::$iso_3166_countryCodes[$this->country];
        return '';
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
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