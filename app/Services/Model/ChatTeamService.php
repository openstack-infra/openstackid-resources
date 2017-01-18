<?php namespace services\model;
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
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ChatTeam;
use models\main\ChatTeamInvitation;
use models\main\ChatTeamMember;
use models\main\ChatTeamPermission;
use models\main\ChatTeamPushNotificationMessage;
use models\main\IChatTeamInvitationRepository;
use models\main\IChatTeamPushNotificationMessageRepository;
use models\main\IChatTeamRepository;
use models\main\IMemberRepository;
use models\main\Member;
use models\oauth2\IResourceServerContext;
use services\apis\IPushNotificationApi;
use utils\PagingInfo;

/**
 * Class ChatTeamService
 * @package services\model
 */
final class ChatTeamService implements IChatTeamService
{
    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * @var IChatTeamRepository
     */
    private $repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var IChatTeamInvitationRepository
     */
    private $invitation_repository;

    /**
     * @var IChatTeamPushNotificationMessageRepository
     */
    private $chat_message_repository;

    /**
     * @var IResourceServerContext
     */
    private $resource_server_context;

    /**
     * @var IPushNotificationApi
     */
    private $push_sender_service;


    public function __construct
    (
        IMemberRepository $member_repository,
        IChatTeamInvitationRepository $invitation_repository,
        IChatTeamPushNotificationMessageRepository $chat_message_repository,
        IChatTeamRepository $repository,
        IResourceServerContext $resource_server_context,
        IPushNotificationApi $push_sender_service,
        ITransactionService $tx_service
    )
    {
        $this->invitation_repository   = $invitation_repository;
        $this->chat_message_repository = $chat_message_repository;
        $this->repository              = $repository;
        $this->member_repository       = $member_repository;
        $this->resource_server_context = $resource_server_context;
        $this->push_sender_service     = $push_sender_service;
        $this->tx_service              = $tx_service;
    }

    /**
     * @param array $data
     * @param Member $owner
     * @return ChatTeam
     */
    function addTeam(array $data, Member $owner)
    {
        return $this->tx_service->transaction(function () use($data, $owner){
            $team = new ChatTeam();
            $team->setName($data['name']);
            if(isset($data['description']))
                $team->setDescription($data['description']);
            $team->setOwner($owner);
            $team_member = $team->createMember($owner, ChatTeamPermission::Admin);
            $team->addMember($team_member);
            $this->repository->add($team);
            return $team;
        });
    }

    /**
     * @param array $data
     * @param int $team_id
     * @return ChatTeam
     */
    function updateTeam(array $data, $team_id){
        return $this->tx_service->transaction(function () use($data, $team_id){
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) throw new EntityNotFoundException();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) throw new EntityNotFoundException();

            $team = $this->repository->getById($team_id);
            if(is_null($team)) throw new EntityNotFoundException();

            if(!$team->isAdmin($current_member))
                throw new EntityNotFoundException();

            $team->setName($data['name']);
            if(isset($data['description']))
                $team->setDescription($data['description']);
            $this->repository->add($team);
            return $team;
        });
    }

    /**
     * @param int $team_id
     * @return void
     * @throws EntityNotFoundException
     */
    function deleteTeam($team_id)
    {
       $this->tx_service->transaction(function() use($team_id){

           $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
           if (is_null($current_member_id)) throw new EntityNotFoundException();

           $current_member = $this->member_repository->getById($current_member_id);
           if (is_null($current_member)) throw new EntityNotFoundException();

           $team = $this->repository->getById($team_id);
           if(is_null($team)) throw new EntityNotFoundException();
           if(!$team->isAdmin($current_member))
               throw new EntityNotFoundException();

           $this->repository->delete($team);
       });
    }

    /**
     * @param int $team_id
     * @param int $invitee_id
     * @param string $permission
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return ChatTeamInvitation
     */
    function addMember2Team($team_id,  $invitee_id, $permission = ChatTeamPermission::Read)
    {
        return $this->tx_service->transaction(function() use($team_id, $invitee_id, $permission){

            $team = $this->repository->getById($team_id);
            if(is_null($team)) throw new EntityNotFoundException();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) throw new EntityNotFoundException();

            $inviter = $this->member_repository->getById($current_member_id);
            if (is_null($inviter)) throw new EntityNotFoundException();

            $invitee  = $this->member_repository->getById($invitee_id);
            if(is_null($invitee)) throw new EntityNotFoundException();

            if(!$team->isAdmin($inviter)) throw new EntityNotFoundException();

            if($team->isMember($invitee))
                throw new ValidationException(sprintf('member id %s already is a member of team id %s', $invitee_id, $team_id));

            if($team->isAlreadyInvited($invitee))
                throw new ValidationException(sprintf('member id %s has a pending invitation on team id %s', $invitee_id, $team_id));

            $invitation = $team->createInvitation($inviter, $invitee, $permission);

            $team->addInvitation($invitation);

            $this->repository->add($team);

            return $invitation;

        });
    }

    /**
     * @param int $invitation_id
     * @param int $invitee_id
     * @return ChatTeamMember
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function acceptInvitation($invitation_id, $invitee_id)
    {
       return $this->tx_service->transaction(function() use($invitation_id, $invitee_id){

           $invitee = $this->member_repository->getById($invitee_id);
           if(is_null($invitee))
               throw new EntityNotFoundException();

           $invitation = $this->invitation_repository->getById($invitation_id);
           if(is_null($invitation))
               throw new EntityNotFoundException();

           if($invitation->getInviteeId() != $invitee_id)
               throw new EntityNotFoundException();

           if($invitation->isAccepted())
               throw new ValidationException(sprintf('invitation id %s is already accepted!', $invitee_id));

           $invitation->accept();

           $team = $invitation->getTeam();

           if($team->isMember($invitee))
               throw new ValidationException(sprintf('invitee id %s is already member of team id %s!', $invitee_id, $team->getId()));

           $team_member = $team->createMember($invitee, $invitation->getPermission());

           $team->addMember($team_member);

           return $team_member;
       });
    }

    /**
     * @param int $invitation_id
     * @param int $invitee_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function declineInvitation($invitation_id, $invitee_id)
    {
        $this->tx_service->transaction(function() use($invitation_id, $invitee_id){

            $invitee = $this->member_repository->getById($invitee_id);
            if(is_null($invitee))
                throw new EntityNotFoundException();

            $invitation = $this->invitation_repository->getById($invitation_id);
            if(is_null($invitation))
                throw new EntityNotFoundException();

            if($invitation->getInviteeId() != $invitee_id)
                throw new EntityNotFoundException();

            if($invitation->isAccepted())
                throw new ValidationException(sprintf('invitation id %s is already accepted!', $invitee_id));

            $this->invitation_repository->delete($invitation);
        });
    }

    /**
     * @param int $team_id
     * @param int $member_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function removeMemberFromTeam($team_id, $member_id)
    {
        $this->tx_service->transaction(function() use($member_id, $team_id){

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) throw new EntityNotFoundException();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) throw new EntityNotFoundException();

            $team_member = $this->member_repository->getById($member_id);
            if (is_null($team_member)) throw new EntityNotFoundException();

            $team = $this->repository->getById($team_id);
            if(is_null($team)) throw new EntityNotFoundException();

            if(!$team->isAdmin($current_member))
                throw new EntityNotFoundException();

            if(!$team->isMember($team_member))
                throw new ValidationException(sprintf('member id %s  is not a member of team id %s', $member_id, $team_id));

            $team->removeMember($team_member);

        });
    }

    /**
     * @param int $team_id
     * @param array $values
     * @return ChatTeamPushNotificationMessage
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    function postMessage($team_id, array $values)
    {
        return $this->tx_service->transaction(function() use($team_id, $values){

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) throw new EntityNotFoundException();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) throw new EntityNotFoundException();

            $team = $this->repository->getById($team_id);
            if(is_null($team)) throw new EntityNotFoundException();

            if(!$team->canPostMessages($current_member))
                throw new ValidationException(sprintf('you do not have permissions to post messages to team id %s', $team_id));

            $message = $team->createMessage($current_member, $values['body'], $values['priority']);

            $team->addMessage($message) ;

            return $message;
       });
    }

    /**
     * @param int $batch_size
     * @return int
     */
    function sendMessages($batch_size = 1000)
    {
        return $this->tx_service->transaction(function() use($batch_size){
           echo(sprintf('calling ChatTeamService.sendMessages(%s)', $batch_size)).PHP_EOL;

           $teams_ids = $this->repository->getAllTeamsIdsWithPendingMessages2Sent();
           $qty       = 0;

           foreach($teams_ids as $team_id) {

               echo(sprintf('processing messages for team id %s', $team_id)).PHP_EOL;
               $messages = $this->chat_message_repository->getAllNotSentByTeamPaginated
               (
                   $team_id,
                   new PagingInfo(1, $batch_size)
               );
               echo(sprintf('found %s messages for team id %s, send them...', $team_id, $messages->getTotal())).PHP_EOL;
               $team_messages_counter = 0;
               foreach ($messages->getItems() as $message){

                   $data  = [
                       'id'              => intval($message->getId()),
                       'type'            => ChatTeamPushNotificationMessage::PushType,
                       'body'            => $message->getMessage(),
                       'from_id'         => intval($message->getOwner()->getId()),
                       'from_first_name' => $message->getOwner()->getFirstName(),
                       'from_last_name'  => $message->getOwner()->getLastName(),
                       'created_at'      => intval($message->getCreated()->getTimestamp()),
                   ];

                   $this->push_sender_service->sendPush([sprintf('team_%s', $team_id)], $data);
                   $message->markSent();
                   ++$qty;
                   ++$team_messages_counter;
               }

               echo(sprintf('sent %s messages for team id %s', $team_messages_counter, $team_id)).PHP_EOL;
           }
           return $qty;
        });
    }
}