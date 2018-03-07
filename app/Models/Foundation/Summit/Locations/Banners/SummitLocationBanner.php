<?php namespace App\Models\Foundation\Summit\Locations\Banners;
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
use models\summit\SummitAbstractLocation;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitLocationBannerRepository")
 * @ORM\Table(name="SummitLocationBanner")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitLocationBanner" = "SummitLocationBanner",
 *     "ScheduledSummitLocationBanner" = "ScheduledSummitLocationBanner"})
 * Class SummitLocationBanner
 * @package App\Models\Foundation\Summit\Locations\Banners
 */
class SummitLocationBanner extends SilverstripeBaseModel
{
    const TypePrimary   = 'Primary';
    const TypeSecondary = 'Secondary';
    const ClassName     = 'SummitLocationBanner';

    /**
     * @return string
     */
    public function getClassName(){
        return SummitLocationBanner::ClassName;
    }

    /**
     * @ORM\Column(name="Title", type="string")
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="Content", type="string")
     * @var string
     */
    protected $content;

    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="Enabled", type="boolean")
     * @var bool
     */
    protected $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitAbstractLocation", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="LocationID", referencedColumnName="ID", onDelete="SET NULL")
     * @var SummitAbstractLocation
     */
    protected $location;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

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
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return SummitAbstractLocation
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param SummitAbstractLocation $location
     */
    public function setLocation(SummitAbstractLocation $location)
    {
        $this->location = $location;
    }

    public function clearLocation(){
        $this->location = null;
    }

    /**
     * @return bool
     */
    public function hasLocation(){
        return $this->getLocationId() > 0;
    }

    /**
     * @return int
     */
    public function getLocationId()
    {
        try {
            return !is_null($this->location)? $this->location->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }

}