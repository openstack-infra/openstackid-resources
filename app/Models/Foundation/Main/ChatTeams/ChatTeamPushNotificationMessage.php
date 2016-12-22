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
 * @ORM\Table(name="ChatTeamPushNotificationMessage")
 * @ORM\Entity(repositoryClass="repositories\main\DoctrineChatTeamPushNotificationMessageRepository")
 * Class ChatTeamPushNotificationMessage
 * @package models\summit
 */
class ChatTeamPushNotificationMessage extends PushNotificationMessage
{
    const PushType = 'TEAM_MESSAGE';

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
     * @ORM\ManyToOne(targetEntity="models\main\ChatTeam")
     * @ORM\JoinColumn(name="ChatTeamID", referencedColumnName="ID")
     * @var ChatTeam
     */
    private $team;
}