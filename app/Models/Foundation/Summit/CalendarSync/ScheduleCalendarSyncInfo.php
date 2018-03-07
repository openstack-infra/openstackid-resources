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
use Doctrine\ORM\Mapping AS ORM;
use models\main\Member;
use models\summit\SummitAbstractLocation;
use models\summit\SummitEvent;
use models\summit\SummitVenueRoom;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineScheduleCalendarSyncInfoRepository")
 * @ORM\Table(name="ScheduleCalendarSyncInfo")
 * @package models\summit\CalendarSync
 */
class ScheduleCalendarSyncInfo extends SilverstripeBaseModel
{

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", inversedBy="schedule_sync_info")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID", nullable=true, onDelete="CASCADE")
     * @var Member
     */
    private $member;

    /**
     * @ORM\Column(name="SummitEventID", type="integer")
     * @var int
     */
    private $summit_event_id;

    /**
     * @return mixed
     */
    public function getSummitEventId()
    {
        return $this->summit_event_id;
    }

    /**
     * @param mixed $summit_event_id
     */
    public function setSummitEventId($summit_event_id)
    {
        $this->summit_event_id = $summit_event_id;
    }

    /**
     * @return SummitEvent
     */
    public function getSummitEvent()
    {
        $id = $this->summit_event_id;
        try {
            $event = $this->getEM()->find(SummitEvent::class, $id);
        }
        catch(\Exception $ex){
            return null;
        }
        return $event;
    }

    /**
     * @param SummitEvent $summit_event
     */
    public function setSummitEvent($summit_event)
    {
        $this->summit_event_id = $summit_event->getId();
    }

    /**
     * @ORM\Column(name="LocationID", type="integer")
     * @var int
     */
    private $location_id;

    /**
     * @ORM\Column(name="ExternalId", type="string")
     * @var string
     */
    private $external_id;

    /**
     * @ORM\Column(name="ETag", type="string")
     * @var string
     */
    private $etag;

    /**
     * @ORM\Column(name="VCard", type="string")
     * @var string
     */
    private $vcard;

    /**
     * @ORM\Column(name="CalendarEventExternalUrl", type="string")
     * @var string
     */
    private $external_url;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\CalendarSync\CalendarSyncInfo", inversedBy="synchronized_events")
     * @ORM\JoinColumn(name="CalendarSyncInfoID", referencedColumnName="ID", nullable=true )
     * @var CalendarSyncInfo
     */
    private $calendar_sync_info;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }


    public function clearOwner(){
        $this->member = null;
        $this->event  = null;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTime
     */
    public function getLastEdited()
    {
        return $this->last_edited;
    }

    /**
     * @param \DateTime $last_edited
     */
    public function setLastEdited($last_edited)
    {
        $this->last_edited = $last_edited;
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
     * @return string
     */
    public function getExternalUrl()
    {
        return $this->external_url;
    }

    /**
     * @param string $external_url
     */
    public function setExternalUrl($external_url)
    {
        $this->external_url = $external_url;
    }

    /**
     * @return CalendarSyncInfo
     */
    public function getCalendarSyncInfo()
    {
        return $this->calendar_sync_info;
    }

    /**
     * @param CalendarSyncInfo $calendar_sync_info
     */
    public function setCalendarSyncInfo($calendar_sync_info)
    {
        $this->calendar_sync_info = $calendar_sync_info;
    }

    /**
     * @return SummitAbstractLocation
     */
    public function getLocation()
    {
        $id = $this->location_id;
        try {
            $location = $this->getEM()->find(SummitAbstractLocation::class, $id);
        }
        catch(\Exception $ex){
            return null;
        }
        return $location;
    }

    /**
     * @param int location_id
     */
    public function setLocationId($location_id)
    {
        $this->location_id = $location_id;
    }

    /**
     * @return string
     */
    public function getVCard()
    {
        return $this->vcard;
    }

    /**
     * @param string $vcard
     */
    public function setVCard($vcard)
    {
        $this->vcard = $vcard;
    }

    /**
     * @return string
     */
    public function toJson(){
        return json_encode([
            'external_id'  => $this->external_id,
            'etag'         => $this->etag,
            'external_url' => $this->external_url,
            'vcard'        => $this->vcard,
        ]);
    }

    /**
     * @param string $str_json
     * @return ScheduleCalendarSyncInfo
     */
    public static function buildFromJson($str_json){
        $res = json_decode($str_json, true);
        $o = new ScheduleCalendarSyncInfo();
        $o->setExternalId($res['external_id']);
        $o->setEtag($res['etag']);
        $o->setExternalUrl($res['external_url']);
        $o->setVCard($res['vcard']);
        return $o;
    }
}