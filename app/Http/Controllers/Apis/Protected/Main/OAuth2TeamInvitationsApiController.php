<?php namespace App\Http\Controllers;
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
use Illuminate\Support\Facades\Log;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IChatTeamInvitationRepository;
use models\oauth2\IResourceServerContext;
use ModelSerializers\SerializerRegistry;
use services\model\IChatTeamService;
use utils\PagingResponse;
use Illuminate\Support\Facades\Input;
/**
 * Class OAuth2TeamInvitationsApiController
 * @package App\Http\Controllers
 */
final class OAuth2TeamInvitationsApiController extends OAuth2ProtectedController
{
    /**
     * @var IChatTeamService
     */
    private $service;

    /**
     * OAuth2TeamInvitationsApiController constructor.
     * @param IChatTeamService $service
     * @param IChatTeamInvitationRepository $repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        IChatTeamService $service,
        IChatTeamInvitationRepository $repository,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->repository = $repository;
        $this->service    = $service;
    }

    /**
     * @return mixed
     */
    public function getMyInvitations(){

        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $invitations = $this->repository->getInvitationsByInvitee($current_member_id);

            $response    = new PagingResponse
            (
                count($invitations),
                count($invitations),
                1,
                1,
                $invitations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getMyPendingInvitations(){

        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $invitations = $this->repository->getPendingInvitationsByInvitee($current_member_id);

            $response    = new PagingResponse
            (
                count($invitations),
                count($invitations),
                1,
                1,
                $invitations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getMyAcceptedInvitations(){

        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $invitations = $this->repository->getAcceptedInvitationsByInvitee($current_member_id);

            $response    = new PagingResponse
            (
                count($invitations),
                count($invitations),
                1,
                1,
                $invitations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $invitation_id
     * @return mixed
     */
    public function acceptInvitation($invitation_id){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $team_member = $this->service->acceptInvitation($invitation_id, $current_member_id);
            return $this->created(SerializerRegistry::getInstance()->getSerializer($team_member)->serialize($expand = ''));
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $invitation_id
     * @return mixed
     */
    public function declineInvitation($invitation_id){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();
            $this->service->declineInvitation($invitation_id, $current_member_id);
            return $this->deleted();
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}