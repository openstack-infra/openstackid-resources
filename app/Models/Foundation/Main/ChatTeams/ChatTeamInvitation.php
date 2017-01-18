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

use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ChatTeamInvitation")
 * @ORM\Entity(repositoryClass="repositories\main\DoctrineChatTeamInvitationRepository")
 * Class ChatTeamInvitation
 * @package models\main
 */
class ChatTeamInvitation extends SilverstripeBaseModel
{

    public function __construct()
    {
        parent::__construct();
        $this->is_accepted = false;
    }

    /**
     * @ORM\Column(name="Permission", type="string")
     * @var string
     */
    private $permission;

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param string $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }

    /**
     * @return ChatTeam
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return int
     */
    public function getTeamId(){
        try{
            return $this->team->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @param ChatTeam $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @return Member
     */
    public function getInvitee()
    {
        return $this->invitee;
    }

    /**
     * @return int
     */
    public function getInviteeId(){
        try{
            return $this->invitee->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @param Member $invitee
     */
    public function setInvitee($invitee)
    {
        $this->invitee = $invitee;
    }

    /**
     * @return Member
     */
    public function getInviter()
    {
        return $this->inviter;
    }

    /**
     * @return int
     */
    public function getInviterId(){
        try{
            return $this->inviter->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @param Member $inviter
     */
    public function setInviter($inviter)
    {
        $this->inviter = $inviter;
    }

    /**
     * @ORM\Column(name="Accepted", type="boolean")
     * @var bool
     */
    private $is_accepted;

    /**
     * @ORM\Column(name="AcceptedDate", type="datetime")
     * @var \DateTime
     */
    private $accepted_date;

    /**
     * @return bool
     */
    public function getIsAccepted()
    {
        return $this->is_accepted;
    }

    /**
     * @return bool
     */
    public function isAccepted(){
        return $this->getIsAccepted();
    }

    /**
     * @return bool
     */
    public function isPending(){
        return !$this->getIsAccepted();
    }

    public function accept()
    {
        $this->is_accepted   = true;
        $now                 = new \DateTime('now', new \DateTimeZone(SilverstripeBaseModel::DefaultTimeZone));
        $this->accepted_date = $now;
    }

    /**
     * @return \DateTime
     */
    public function getAcceptedDate()
    {
        return $this->accepted_date;
    }

    /**
     * @param \DateTime $accepted_date
     */
    public function setAcceptedDate($accepted_date)
    {
        $this->accepted_date = $accepted_date;
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\ChatTeam")
     * @ORM\JoinColumn(name="TeamID", referencedColumnName="ID")
     * @var ChatTeam
     */
    private $team;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="InviteeID", referencedColumnName="ID")
     * @var Member
     */
    private $invitee;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="InviterID", referencedColumnName="ID")
     * @var Member
     */
    private $inviter;
}