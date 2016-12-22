<?php namespace services\model;
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

use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ChatTeamInvitation;
use models\main\ChatTeam;
use models\main\ChatTeamMember;
use models\main\ChatTeamPermission;
use models\main\ChatTeamPushNotificationMessage;
use models\main\Member;

/**
 * Interface IChatTeamService
 * @package services\model
 */
interface IChatTeamService
{
    /**
     * @param array $data
     * @param Member $owner
     * @return ChatTeam
     */
    function addTeam(array $data, Member $owner);

    /**
     * @param array $data
     * @param int $team_id
     * @return ChatTeam
     */
    function updateTeam(array $data, $team_id);

    /**
     * @param int $team_id
     * @return void
     * @throws EntityNotFoundException
     */
    function deleteTeam($team_id);

    /**
     * @param int $team_id
     * @param int $invitee_id
     * @param string $permission
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return ChatTeamInvitation
     */
    function addMember2Team($team_id, $invitee_id, $permission = ChatTeamPermission::Read);

    /**
     * @param int $invitation_id
     * @param int $invitee_id
     * @return ChatTeamMember
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function acceptInvitation($invitation_id, $invitee_id);

    /**
     * @param int $invitation_id
     * @param int $invitee_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function declineInvitation($invitation_id, $invitee_id);

    /**
     * @param int $team_id
     * @param int $member_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function removeMemberFromTeam($team_id, $member_id);

    /**
     * @param int $team_id
     * @param array $values
     * @return ChatTeamPushNotificationMessage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function postMessage($team_id, array $values);
}