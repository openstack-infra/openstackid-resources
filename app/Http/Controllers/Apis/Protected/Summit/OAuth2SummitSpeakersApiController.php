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
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use libs\utils\HTMLCleaner;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\Group;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISpeakerService;
use services\model\ISummitService;
use utils\FilterParser;
use utils\FilterParserException;
use utils\OrderParser;
use utils\PagingInfo;
use Illuminate\Http\Request as LaravelRequest;
/**
 * Class OAuth2SummitSpeakersApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitSpeakersApiController extends OAuth2ProtectedController
{
    /**
     * @var ISpeakerService
     */
    private $service;

    /**
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var ISummitEventRepository
     */
    private $event_repository;

    /**
     * @var IEventFeedbackRepository
     */
    private $event_feedback_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;


    /**
     * OAuth2SummitSpeakersApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param IEventFeedbackRepository $event_feedback_repository
     * @param IMemberRepository $member_repository
     * @param ISpeakerService $service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        IEventFeedbackRepository $event_feedback_repository,
        IMemberRepository $member_repository,
        ISpeakerService $service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->repository                = $summit_repository;
        $this->speaker_repository        = $speaker_repository;
        $this->event_repository          = $event_repository;
        $this->member_repository         = $member_repository;
        $this->event_feedback_repository = $event_feedback_repository;
        $this->service                   = $service;
    }

    /**
     *  Speakers endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSpeakers($summit_id)
    {
        try {
            $serializer_type = SerializerRegistry::SerializerType_Public;
            $summit          = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $values = Input::all();

            $rules = array
            (
                'page'     => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:10|max:100',
            );

            $validation = Validator::make($values, $rules);

            if ($validation->fails())
            {
                $messages = $validation->messages()->toArray();

                return $this->error412($messages);
            }

            // default values
            $page     = 1;
            $per_page = 10;

            if (Input::has('page'))
            {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter'))
            {
                $filter = FilterParser::parse(Input::get('filter'), [

                    'first_name' => ['=@', '=='],
                    'last_name'  => ['=@', '=='],
                    'email'      => ['=@', '=='],
                    'id'         => ['=='],
                ]);
            }

            $order = null;
            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [
                    'first_name',
                    'last_name',
                    'id',
                    'email',
                ]);
            }

            $current_member_id = $this->resource_server_context->getCurrentUserId();
            if(!is_null($current_member_id) && $member = $this->member_repository->getById($current_member_id)){
                if($member->isOnGroup(Group::SummitAdministrators)){
                    $serializer_type = SerializerRegistry::SerializerType_Private;
                }
            }
            $result = $this->speaker_repository->getSpeakersBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray(Request::input('expand', ''),[],[],['summit_id' => $summit_id, 'published' => true, 'summit' => $summit], $serializer_type)
            );
        }
        catch(ValidationException $ex1){
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }


    public function getAll(){
        try {

            $values = Input::all();

            $rules = array
            (
                'page'     => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:10|max:100',
            );

            $validation = Validator::make($values, $rules);

            if ($validation->fails())
            {
                $messages = $validation->messages()->toArray();

                return $this->error412($messages);
            }

            // default values
            $page     = 1;
            $per_page = 10;

            if (Input::has('page'))
            {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter'))
            {
                $filter = FilterParser::parse(Input::get('filter'), [

                    'first_name' => ['=@', '=='],
                    'last_name'  => ['=@', '=='],
                    'email'      => ['=@', '=='],
                    'id'         => ['=='],
                ]);
            }

            $order = null;
            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [
                    'id',
                    'email',
                    'first_name',
                    'last_name',
                ]);
            }

            $result = $this->speaker_repository->getAllByPage(new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray(Request::input('expand', ''),[],[])
            );
        }
        catch(ValidationException $ex1){
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message'=> $ex2->getMessage()));
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $speaker_id
     * @return mixed
     */
    public function getSpeaker($summit_id, $speaker_id)
    {
        try
        {
            $serializer_type = SerializerRegistry::SerializerType_Public;
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $speaker = CheckSpeakerStrategyFactory::build(CheckSpeakerStrategyFactory::Me, $this->resource_server_context)->check($speaker_id, $summit);
            if (is_null($speaker)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserId();
            if(!is_null($current_member_id) && $member = $this->member_repository->getById($current_member_id)){
                if($member->isOnGroup(Group::SummitAdministrators)){
                    $serializer_type = SerializerRegistry::SerializerType_Private;
                }
            }

            return $this->ok
            (
                SerializerRegistry::getInstance()->getSerializer($speaker, $serializer_type)->serialize
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    ['summit_id' => $summit_id, 'published' => true, 'summit' => $summit]
                )
            );

        }
        catch(ValidationException $ex1){
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
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

    public function addSpeaker($summit_id){
        try {
            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = array
            (
                'title'             => 'required|string|max:100',
                'first_name'        => 'required|string|max:100',
                'last_name'         => 'required|string|max:100',
                'bio'               => 'sometimes|string',
                'irc'               => 'sometimes|string|max:50',
                'twitter'           => 'sometimes|string|max:50',
                'member_id'         => 'sometimes|integer',
                'email'             => 'sometimes|string|max:50',
                'on_site_phone'     => 'sometimes|string|max:50',
                'registered'        => 'sometimes|boolean',
                'confirmed'         => 'sometimes|boolean',
                'checked_in'        => 'sometimes|boolean',
                'registration_code' => 'sometimes|string',
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

            $fields = [
                'title',
                'bio',
            ];

            $speaker = $this->service->addSpeaker($summit, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->created(SerializerRegistry::getInstance()->getSerializer($speaker)->serialize());
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    public function updateSpeaker($summit_id, $speaker_id){
        try {
            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $speaker = $this->speaker_repository->getById($speaker_id);
            if (is_null($speaker)) return $this->error404();

            $rules = array
            (
                'title'             => 'sometimes|string|max:100',
                'first_name'        => 'sometimes|string|max:100',
                'last_name'         => 'sometimes|string|max:100',
                'bio'               => 'sometimes|string',
                'irc'               => 'sometimes|string|max:50',
                'twitter'           => 'sometimes|string|max:50',
                'member_id'         => 'sometimes|integer',
                'email'             => 'sometimes|string|max:50',
                'on_site_phone'     => 'sometimes|string|max:50',
                'registered'        => 'sometimes|boolean',
                'confirmed'         => 'sometimes|boolean',
                'checked_in'        => 'sometimes|boolean',
                'registration_code' => 'sometimes|string',
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

            $fields = [
                'title',
                'bio',
            ];

            $speaker = $this->service->updateSpeaker($summit, $speaker, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->created(SerializerRegistry::getInstance()->getSerializer($speaker)->serialize());
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    public function addSpeakerPhoto(LaravelRequest $request, $speaker_id){

        try {

            $file = $request->file('file');
            if (is_null($file)) {
                return $this->error412(array('file param not set!'));
            }

            $res = $this->service->addSpeakerAttachment($speaker_id, $file);

            return !is_null($res) ? $this->created($res->getId()) : $this->error400();
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        }
        catch(ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412(array($ex2->getMessage()));
        }
        catch(\HTTP401UnauthorizedException $ex3)
        {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}