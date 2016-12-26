<?php namespace models\main;
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

use Doctrine\ORM\Mapping AS ORM;
use models\utils\SilverstripeBaseModel;

/**
 * @ORM\Entity
 * @ORM\Table(name="PushNotificationMessage")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"PushNotificationMessage" = "PushNotificationMessage", "SummitPushNotification" = "models\summit\SummitPushNotification", "ChatTeamPushNotificationMessage" = "ChatTeamPushNotificationMessage"})
 * Class PushNotificationMessage
 * @package models\main
 */
class PushNotificationMessage extends SilverstripeBaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->is_sent = false;
    }

    /**
     * @ORM\Column(name="Message", type="string")
     * @var string
     */
    protected $message;

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @ORM\Column(name="Priority", type="string")
     * @var string
     */
    protected $priority;

    /**
     * @ORM\Column(name="SentDate", type="datetime")
     * @var \DateTime
     */
    protected $sent_date;

    /**
     * @ORM\Column(name="IsSent", type="boolean")
     * @var bool
     */
    protected $is_sent;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    protected $owner;

    /**
     * @return int
     */
    public function getOwnerId(){
        try{
            return $this->owner->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
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

}