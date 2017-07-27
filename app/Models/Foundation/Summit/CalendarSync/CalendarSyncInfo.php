<?php namespace models\summit\CalendarSync;
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use models\main\Member;
use models\main\SummitMemberSchedule;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="CalendarSyncInfo")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"CalendarSyncInfo" = "CalendarSyncInfo", "CalendarSyncInfoCalDav" = "CalendarSyncInfoCalDav", "CalendarSyncInfoOAuth2" = "CalendarSyncInfoOAuth2"})
 * Class CalendarSyncInfo
 * @package models\summit\CalendarSync
 */
class CalendarSyncInfo extends SilverstripeBaseModel
{
    const ProviderGoogle  = 'Google';
    const ProviderOutlook = 'Outlook';
    const ProvideriCloud  = 'iCloud';
    /**
     * CalendarSyncInfo constructor.
     */
    public function __construct()
    {
        $this->synchronized_events = new ArrayCollection();
    }

    /**
     * @ORM\Column(name="Provider", type="string")
     * @var string
     */
    protected $provider;

    /**
     * @ORM\Column(name="CalendarExternalId", type="string")
     * @var string
     */
    protected $external_id;

    /**
     * @ORM\Column(name="ETag", type="string")
     * @var string
     */
    protected $etag;

    /**
     * @ORM\Column(name="Revoked", type="boolean")
     * @var bool
     */
    protected $revoked;

    use SummitOwned;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", cascade={"persist"})
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    protected $owner;

    /**
     * @ORM\OneToMany(targetEntity="ScheduleCalendarSyncInfo", mappedBy="sync_info", cascade={"persist"}, orphanRemoval=true)
     * @var ScheduleCalendarSyncInfo[]
     */
    protected $synchronized_events;

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @param string $external_id
     */
    public function setExternalId($external_id)
    {
        $this->external_id = $external_id;
    }

    /**
     * @return string
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * @param string $etag
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
    }

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return SummitMemberSchedule[]
     */
    public function getSynchronizedEvents()
    {
        return $this->synchronized_events;
    }

    /**
     * @return bool
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    /**
     * @param bool $revoked
     */
    public function setRevoked($revoked)
    {
        $this->revoked = $revoked;
    }

    public function clearOwner(){
        $this->owner = null;
    }

}