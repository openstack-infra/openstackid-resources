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

use models\summit\SummitEventType;
use Doctrine\ORM\Mapping AS ORM;
/**
 * Class PresentationType
 * @ORM\Entity
 * @ORM\Table(name="PresentationType")
 * @package models\summit
 */
class PresentationType extends SummitEventType
{

    /**
     * @ORM\Column(name="MaxSpeakers", type="integer")
     * @var int
     */
    protected $max_speakers;

    /**
     * @ORM\Column(name="MinSpeakers", type="integer")
     * @var int
     */
    protected $min_speakers;

    /**
     * @ORM\Column(name="MaxModerators", type="integer")
     * @var int
     */
    protected $max_moderators;

    /**
     * @ORM\Column(name="MinModerators", type="integer")
     * @var int
     */
    protected $min_moderators;

    /**
     * @ORM\Column(name="UseSpeakers", type="boolean")
     * @var bool
     */
    protected $use_speakers;

    /**
     * @ORM\Column(name="AreSpeakersMandatory", type="boolean")
     * @var bool
     */
    protected $are_speakers_mandatory;

    /**
     * @ORM\Column(name="UseModerator", type="boolean")
     * @var bool
     */
    protected $use_moderator;

    /**
     * @ORM\Column(name="IsModeratorMandatory", type="boolean")
     * @var bool
     */
    protected $is_moderator_mandatory;

    /**
     * @ORM\Column(name="ShouldBeAvailableOnCFP", type="boolean")
     * @var bool
     */
    protected $should_be_available_on_cfp;

    /**
     * @ORM\Column(name="ModeratorLabel", type="string")
     * @var string
     */
    protected $moderator_label;

    /**
     * @param Summit $summit
     * @param string $type
     * @return bool
     */
    public static function IsPresentationEventType(Summit $summit, $type){

        try{
            $sql = <<<SQL
            SELECT COUNT(DISTINCT(PresentationType.ID))
            FROM PresentationType
            INNER JOIN SummitEventType ON SummitEventType.ID = PresentationType.ID
            WHERE SummitEventType.SummitID = :summit_id 
            AND SummitEventType.Type = :type
SQL;
            $stmt = self::prepareRawSQLStatic($sql);
            $stmt->execute(['summit_id' => $summit->getId(), 'type' => $type]);
            $res = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return count($res) > 0 ;
        }
        catch (\Exception $ex){

        }
        return false;
    }

    /**
     * @return array()
     */
    static public function presentationTypes(){
        return [IPresentationType::Presentation, IPresentationType::Keynotes, IPresentationType::LightingTalks, IPresentationType::Panel];
    }

    /**
     * @return int
     */
    public function getMaxSpeakers()
    {
        return $this->max_speakers;
    }

    /**
     * @return int
     */
    public function getMinSpeakers()
    {
        return $this->min_speakers;
    }

    /**
     * @return int
     */
    public function getMaxModerators()
    {
        return $this->max_moderators;
    }

    /**
     * @return int
     */
    public function getMinModerators()
    {
        return $this->min_moderators;
    }

    /**
     * @return bool
     */
    public function isUseSpeakers()
    {
        return $this->use_speakers;
    }

    /**
     * @return bool
     */
    public function isAreSpeakersMandatory()
    {
        return $this->are_speakers_mandatory;
    }

    /**
     * @return bool
     */
    public function isUseModerator()
    {
        return $this->use_moderator;
    }

    /**
     * @return bool
     */
    public function isModeratorMandatory()
    {
        return $this->is_moderator_mandatory;
    }

    /**
     * @return bool
     */
    public function isShouldBeAvailableOnCfp()
    {
        return $this->should_be_available_on_cfp;
    }

    /**
     * @return string
     */
    public function getModeratorLabel()
    {
        return $this->moderator_label;
    }

    public function getClassName(){
        return 'PresentationType';
    }

    const ClassName = 'PRESENTATION_TYPE';

    /**
     * @param int $max_speakers
     */
    public function setMaxSpeakers($max_speakers)
    {
        $this->max_speakers = $max_speakers;
    }

    /**
     * @param int $min_speakers
     */
    public function setMinSpeakers($min_speakers)
    {
        $this->min_speakers = $min_speakers;
    }

    /**
     * @param int $max_moderators
     */
    public function setMaxModerators($max_moderators)
    {
        $this->max_moderators = $max_moderators;
    }

    /**
     * @param int $min_moderators
     */
    public function setMinModerators($min_moderators)
    {
        $this->min_moderators = $min_moderators;
    }

    /**
     * @param bool $use_speakers
     */
    public function setUseSpeakers($use_speakers)
    {
        $this->use_speakers = $use_speakers;
    }

    /**
     * @param bool $are_speakers_mandatory
     */
    public function setAreSpeakersMandatory($are_speakers_mandatory)
    {
        $this->are_speakers_mandatory = $are_speakers_mandatory;
    }

    /**
     * @param bool $use_moderator
     */
    public function setUseModerator($use_moderator)
    {
        $this->use_moderator = $use_moderator;
    }

    /**
     * @param bool $is_moderator_mandatory
     */
    public function setIsModeratorMandatory($is_moderator_mandatory)
    {
        $this->is_moderator_mandatory = $is_moderator_mandatory;
    }

    /**
     * @param bool $should_be_available_on_cfp
     */
    public function setShouldBeAvailableOnCfp($should_be_available_on_cfp)
    {
        $this->should_be_available_on_cfp = $should_be_available_on_cfp;
    }

    /**
     * @param string $moderator_label
     */
    public function setModeratorLabel($moderator_label)
    {
        $this->moderator_label = $moderator_label;
    }

    public function __construct()
    {
        parent::__construct();
        $this->are_speakers_mandatory     = false;
        $this->use_speakers               = false;
        $this->use_moderator              = false;
        $this->is_moderator_mandatory     = false;
        $this->should_be_available_on_cfp = false;
    }
}