<?php namespace models\summit;
/**
 * Copyright 2016 OpenStack Foundation
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
use Doctrine\ORM\Mapping as ORM;
use models\main\CustomDataObject;
use models\main\Group;
use models\main\Member;

/**
 * Class SummitPushNotificationChannel
 * @package models\summit
 */
final class SummitPushNotificationChannel {

    const Everyone  = 'EVERYONE';
    const Speakers  = 'SPEAKERS';
    const Attendees = 'ATTENDEES';
    const Members   = 'MEMBERS';
    const Summit    = 'SUMMIT';
    const Event     = 'EVENT';
    const Group     = 'GROUP';

    /**
     * @return array
     */
    public static function getPublicChannels(){
        return [self::Everyone, self::Speakers, self::Attendees, self::Summit, self::Event, self::Group];
    }

    /**
     * @param string $channel
     * @return bool
     */
    public static function isPublicChannel($channel){
        return in_array($channel, self::getPublicChannels());
    }
}
/**
 * @ORM\Entity
 * @ORM\Table(name="SummitPushNotification")
 * @ORM\Entity(repositoryClass="repositories\summit\DoctrineSummitNotificationRepository")
 * Class SummitPushNotification
 * @package models\summit
 */
class SummitPushNotification extends CustomDataObject
{
    use SummitOwned;

    /**
     * @ORM\Column(name="Channel", type="string")
     * @var string
     */
    private $channel;

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return \DateTime
     */
    public function getSentDate()
    {
        return $this->sent_date;
    }

    /**
     * @param \DateTime $sent_date
     */
    public function setSentDate($sent_date)
    {
        $this->sent_date = $sent_date;
    }

    /**
     * @return boolean
     */
    public function isSent()
    {
        return $this->is_sent;
    }

    /**
     * @return boolean
     */
    public function getIsSent()
    {
        return $this->isSent();
    }

    /**
     * @param boolean $is_sent
     */
    public function setIsSent($is_sent)
    {
        $this->is_sent = $is_sent;
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
     * @return SummitEvent
     */
    public function getSummitEvent()
    {
        return $this->summit_event;
    }

    /**
     * @param SummitEvent $summit_event
     */
    public function setSummitEvent($summit_event)
    {
        $this->summit_event = $summit_event;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param Group $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param ArrayCollection $recipients
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @ORM\Column(name="Message", type="string")
     * @var string
     */
    private $message;

    /**
     * @ORM\Column(name="SentDate", type="datetime")
     * @var \DateTime
     */
    private $sent_date;

    /**
     * @ORM\Column(name="IsSent", type="boolean")
     * @var bool
     */
    private $is_sent;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitEvent")
     * @ORM\JoinColumn(name="EventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    private $summit_event;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Group")
     * @ORM\JoinColumn(name="GroupID", referencedColumnName="ID")
     * @var Group
     */
    private $group;

    /**
     * @ORM\ManyToMany(targetEntity="models\main\Member")
     * @ORM\JoinTable(name="SummitPushNotification_Recipients",
     *      joinColumns={@ORM\JoinColumn(name="SummitPushNotificationID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="MemberID", referencedColumnName="ID")}
     *      )
     */
    private $recipients;

    /**
     * SummitPushNotification constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->recipients = new ArrayCollection();
    }
}