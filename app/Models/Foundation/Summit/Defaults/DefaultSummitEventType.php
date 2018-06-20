<?php namespace App\Models\Foundation\Summit\Defaults;
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
use models\summit\Summit;
use models\summit\SummitEventType;
use models\utils\SilverstripeBaseModel;
/**
 * Class DefaultSummitEventType
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineDefaultSummitEventTypeRepository")
 * @ORM\Table(name="DefaultSummitEventType")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"DefaultSummitEventType" = "DefaultSummitEventType", "DefaultPresentationType" = "DefaultPresentationType"})
 * @package App\Models\Foundation\Summit\Defaults
 */
class DefaultSummitEventType extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="Color", type="string")
     * @var string
     */
    protected $color;

    /**
     * @ORM\Column(name="BlackoutTimes", type="boolean")
     * @var bool
     */
    protected $blackout_times;

    /**
     * @ORM\Column(name="UseSponsors", type="boolean")
     * @var bool
     */
    protected $use_sponsors;

    /**
     * @ORM\Column(name="AreSponsorsMandatory", type="boolean")
     * @var bool
     */
    protected $are_sponsors_mandatory;

    /**
     * @ORM\Column(name="AllowsAttachment", type="boolean")
     * @var bool
     */
    protected $allows_attachment;

    /**
     * @ORM\Column(name="IsPrivate", type="boolean")
     * @var bool
     */
    protected $is_private;

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
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return bool
     */
    public function isBlackoutTimes()
    {
        return $this->blackout_times;
    }

    /**
     * @param bool $blackout_times
     */
    public function setBlackoutTimes($blackout_times)
    {
        $this->blackout_times = $blackout_times;
    }

    /**
     * @return bool
     */
    public function isUseSponsors()
    {
        return $this->use_sponsors;
    }

    /**
     * @param bool $use_sponsors
     */
    public function setUseSponsors($use_sponsors)
    {
        $this->use_sponsors = $use_sponsors;
    }

    /**
     * @return bool
     */
    public function isAreSponsorsMandatory()
    {
        return $this->are_sponsors_mandatory;
    }

    /**
     * @param bool $are_sponsors_mandatory
     */
    public function setAreSponsorsMandatory($are_sponsors_mandatory)
    {
        $this->are_sponsors_mandatory = $are_sponsors_mandatory;
    }

    /**
     * @return bool
     */
    public function isAllowsAttachment()
    {
        return $this->allows_attachment;
    }

    /**
     * @param bool $allows_attachment
     */
    public function setAllowsAttachment($allows_attachment)
    {
        $this->allows_attachment = $allows_attachment;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->is_private;
    }

    /**
     * @param bool $is_private
     */
    public function setIsPrivate($is_private)
    {
        $this->is_private = $is_private;
    }

    protected function newType(){
        return new SummitEventType();
    }

    /**
     * @param Summit $summit
     * @return SummitEventType
     */
    public function buildType(Summit $summit){
        $new_type = $this->newType();
        $new_type->setSummit($summit);
        $new_type->setType($this->type);
        $new_type->setColor($this->color);
        $new_type->setBlackoutTimes($this->blackout_times);
        $new_type->setUseSponsors($this->use_sponsors);
        $new_type->setAreSponsorsMandatory($this->are_sponsors_mandatory);
        $new_type->setAllowsAttachment($this->allows_attachment);
        $new_type->setIsPrivate($this->is_private);
        $new_type->setAsDefault();
        return $new_type;
    }

    public function __construct()
    {
        $this->blackout_times = false;
        $this->use_sponsors = false;
        $this->are_sponsors_mandatory = false;
        $this->allows_attachment = false;
        $this->is_private = false;
    }

}