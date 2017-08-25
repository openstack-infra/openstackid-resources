<?php namespace App\Http\Controllers;
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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use libs\utils\HTMLCleaner;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IChatTeamPushNotificationMessageRepository;
use models\main\IChatTeamRepository;
use models\main\IMemberRepository;
use models\main\PushNotificationMessagePriority;
use models\oauth2\IResourceServerContext;
use ModelSerializers\SerializerRegistry;
use services\model\IChatTeamService;
use utils\Filter;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;
use utils\PagingResponse;
/**
 * Class OAuth2TeamsApiController
 * @package App\Http\Controllers
 */
final class OAuth2TeamsApiController extends OAuth2ProtectedController
{

    /**
     * @var IChatTeamService
     */
    private $service;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var IChatTeamPushNotificationMessageRepository
     */
    private $message_repository;

    /**
     * OAuth2TeamsApiController constructor.
     * @param IChatTeamService $service
     * @param IMemberRepository $member_repository
     * @param IChatTeamPushNotificationMessageRepository $message_repository
     * @param IChatTeamRepository $repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        IChatTeamService $service,
        IMemberRepository $member_repository,
        IChatTeamPushNotificationMessageRepository $message_repository,
        IChatTeamRepository $repository,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->service            = $service;
        $this->repository         = $repository;
        $this->message_repository = $message_repository;
        $this->member_repository  = $member_repository;
    }

    /**
     * @return mixed
     */
    public function getMyTeams(){
        try {

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $teams    = $this->repository->getTeamsByMember($current_member);

            $response = new PagingResponse
            (
                count($teams),
                count($teams),
                1,
                1,
                $teams
            );

            return $this->ok
            (
                $response->toArray
                (
                    $expand    = Input::get('expand',''),
                    $fields    = [],
                    $relations = [],
                    $params    = [
                        'current_member' => $current_member
                    ]
                )
            );

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
     * @param $team_id
     * @return mixed
     */
    public function getMyTeam($team_id){
        try {

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error403();

            $team  = $this->repository->getById($team_id);

            if(is_null($team)) throw new EntityNotFoundException();

            if(!$team->isMember($current_member))
                throw new EntityNotFoundException();

            return $this->ok
            (
                SerializerRegistry::getInstance()
                    ->getSerializer($team)
                    ->serialize
                    (
                        $expand    = Input::get('expand',''),
                        $fields    = [],
                        $relations = [],
                        $params    = [
                            'current_member' => $current_member
                        ]
                    )
            );

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
    public function addTeam(){
        try {

            if(!Request::isJson())
                return $this->error403();

            $data = Input::json();

            $rules = array
            (
                'name'            => 'required|string|max:255',
                'description'     => 'required|sometimes|string|max:512',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $fields = array
            (
                'name',
                'description',
            );

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $team = $this->service->addTeam(HTMLCleaner::cleanData($data->all(), $fields), $current_member);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($team)->serialize($expand = 'owner,members,member'));
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
     * @param $team_id
     * @return mixed
     */
    public function deleteTeam($team_id){
        try {

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $this->service->deleteTeam($team_id);

            return $this->deleted();
        }
        catch (ValidationException $ex1)
        {
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
     * @param $team_id
     * @return mixed
     */
    public function updateTeam($team_id){
        try {

            if(!Request::isJson())
                return $this->error403();

            $data = Input::json();

            $rules = array
            (
                'name'            => 'required|string|max:255',
                'description'     => 'string|max:512',
            );

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $fields = array
            (
                'name',
                'description',
            );

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $team = $this->service->updateTeam(HTMLCleaner::cleanData($data->all(), $fields), $team_id);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($team)->serialize($expand = 'owner,members,member'));
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
     * @param $team_id
     * @return mixed
     */
    public function getMyTeamMessages($team_id){

        $values = Input::all();

        $rules = array
        (
            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        );

        try {


            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }

            // default values
            $page     = 1;
            $per_page = 5;

            if (Input::has('page')) {
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'),  array
                (
                    'owner_id'   => ['=='],
                    'sent_date'  => ['>', '<', '<=', '>=', '=='],
                ));
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'sent_date',
                    'id',
                ));
            }

            if(is_null($filter)) $filter = new Filter();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->member_repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error403();

            $team  = $this->repository->getById($team_id);

            if(is_null($team)) throw new EntityNotFoundException();

            if(!$team->isMember($current_member))
                throw new EntityNotFoundException();

            $data = $this->message_repository->getAllSentByTeamPaginated
            (
                $team_id,
                new PagingInfo($page, $per_page),
                $filter,
                $order
            );

            return $this->ok($data->toArray(Request::input('expand', '')));
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
     * @param $team_id
     * @return mixed
     */
    public function postTeamMessage($team_id){
        try {

            if(!Request::isJson())
                return $this->error403();

            $data = Input::json();

            $rules = array
            (
                'body'     => 'required|string',
                'priority' => 'required|sometimes|string|chat_message_priority',
            );

            $values     = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            if(!isset($values['priority']))
                $values['priority'] =  PushNotificationMessagePriority::Normal;

            $message = $this->service->postMessage($team_id, $values);
            return $this->created(SerializerRegistry::getInstance()->getSerializer($message)->serialize($expand = 'team,owner'));
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
     * @param $team_id
     * @param $member_id
     * @return mixed
     */
    public function addMember2MyTeam($team_id, $member_id){
        try {

            if(!Request::isJson())
                return $this->error403();

            $data = Input::json();

            $rules = array
            (
                'permission' => 'required|string|team_permission',
            );

            $values     = $data->all();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $invitation = $this->service->addMember2Team($team_id, $member_id, $values['permission']);
            return $this->created(SerializerRegistry::getInstance()->getSerializer($invitation)->serialize($expand = 'team,inviter,invitee'));

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
     * @param $team_id
     * @param $member_id
     * @return mixed
     */
    public function removedMemberFromMyTeam($team_id, $member_id){
        try {

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $this->service->removeMemberFromTeam($team_id, $member_id);

            return $this->deleted();
        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

}