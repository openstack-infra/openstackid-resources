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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ChatTeam_Members")
 * Class ChatTeamMember
 * @package models\main
 */
class ChatTeamMember
{
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

    /**
     * @return ChatTeam
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param ChatTeam $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @return bool
     */
    public function isAdmin(){
        return $this->permission == ChatTeamPermission::Admin;
    }

    /**
     * @return bool
     */
    public function canPostMessages(){
        return $this->isAdmin() || $this->permission == ChatTeamPermission::Write;
    }

    /**
     * @return bool
     */
    public function canDeleteMembers(){
        return $this->isAdmin();
    }

    /**
     * @param mixed $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
    }
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="ID", type="integer", unique=true, nullable=false)
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMemberId(){
        try{
            return $this->member->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    public function getTeamId(){
        try{
            return $this->team->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", inversedBy="team_memberships")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\ChatTeam", inversedBy="members")
     * @ORM\JoinColumn(name="ChatTeamID", referencedColumnName="ID")
     * @var ChatTeam
     */
    private $team;

    /**
     * @ORM\Column(name="Permission", type="string")
     * @var string
     */
    private $permission;

}