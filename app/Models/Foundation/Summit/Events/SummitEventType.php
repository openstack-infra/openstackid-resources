<?php namespace models\summit;
/**
 * Copyright 2015 OpenStack Foundation
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
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitEventTypeRepository")
 * @ORM\Table(name="SummitEventType")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitEventType" = "SummitEventType", "PresentationType" = "PresentationType", "SummitGroupEvent" = "SummitGroupEvent", "SummitEventWithFile" = "SummitEventWithFile"})
 */
class SummitEventType extends SilverstripeBaseModel
{
    use SummitOwned;

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
     * @ORM\Column(name="IsDefault", type="boolean")
     * @var bool
     */
    protected $is_default;

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
    public function getBlackoutTimes()
    {
        return $this->blackout_times;
    }

    /**
     * @return bool
     */
    public function isBlackoutTimes(){
        return $this->getBlackoutTimes();
    }

    /**
     * @param bool $blackout_times
     */
    public function setBlackoutTimes($blackout_times)
    {
        $this->blackout_times = $blackout_times;
    }

    /**
     * @param Summit $summit
     * @param string $type
     * @return bool
     */
    static public function IsSummitEventType(Summit $summit, $type){
        return !PresentationType::IsPresentationEventType($summit, $type);
    }

    /**
     * @return bool
     */
    public function isUseSponsors()
    {
        return $this->use_sponsors;
    }

    /**
     * @return bool
     */
    public function isAreSponsorsMandatory()
    {
        return $this->are_sponsors_mandatory;
    }

    /**
     * @return bool
     */
    public function isAllowsAttachment()
    {
        return $this->allows_attachment;
    }

    public function getClassName(){
        return 'SummitEventType';
    }

    const ClassName = 'EVENT_TYPE';

    /**
     * @ORM\Column(name="IsPrivate", type="boolean")
     * @var bool
     */
    protected $is_private;

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    public function setAsDefault()
    {
        $this->is_default = true;
    }

    public function setAsNonDefault()
    {
        $this->is_default = false;
    }

    /**
     * @param bool $use_sponsors
     */
    public function setUseSponsors($use_sponsors)
    {
        $this->use_sponsors = $use_sponsors;
    }

    /**
     * @param bool $are_sponsors_mandatory
     */
    public function setAreSponsorsMandatory($are_sponsors_mandatory)
    {
        $this->are_sponsors_mandatory = $are_sponsors_mandatory;
    }

    /**
     * @param bool $allows_attachment
     */
    public function setAllowsAttachment($allows_attachment)
    {
        $this->allows_attachment = $allows_attachment;
    }

    public function __construct()
    {
        parent::__construct();
        $this->is_default             = false;
        $this->use_sponsors           = false;
        $this->blackout_times         = false;
        $this->are_sponsors_mandatory = false;
        $this->allows_attachment      = false;
        $this->is_private             = false;
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

    /**
     * @return SummitEvent[]
     */
    public function getRelatedPublishedSummitEvents(){
        $query = <<<SQL
SELECT e  
FROM  models\summit\SummitEvent e
WHERE 
e.published = 1
AND e.summit = :summit
AND e.type = :type
SQL;

        $native_query = $this->getEM()->createQuery($query);

        $native_query->setParameter("summit", $this->summit);
        $native_query->setParameter("type", $this);

        $res =  $native_query->getResult();

        return $res;
    }

    /**
     * @return int[]
     */
    public function getRelatedPublishedSummitEventsIds(){
        $query = <<<SQL
SELECT e.id  
FROM  models\summit\SummitEvent e
WHERE 
e.published = 1
AND e.summit = :summit
AND e.type = :type
SQL;

        $native_query = $this->getEM()->createQuery($query);

        $native_query->setParameter("summit", $this->summit);
        $native_query->setParameter("type", $this);

        $res =  $native_query->getResult();

        return $res;
    }

}
