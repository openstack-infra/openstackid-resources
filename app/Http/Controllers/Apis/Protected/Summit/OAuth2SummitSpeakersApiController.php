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
use App\Models\Foundation\Summit\Repositories\ISelectionPlanRepository;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use libs\utils\HTMLCleaner;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use models\summit\PresentationSpeaker;
use ModelSerializers\ISerializerTypeSelector;
use ModelSerializers\SerializerRegistry;
use services\model\ISpeakerService;
use services\model\ISummitService;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;
use Illuminate\Http\Request as LaravelRequest;
use utils\PagingResponse;

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
     * @var ISerializerTypeSelector
     */
    private $serializer_type_selector;

    /**
     * @var ISelectionPlanRepository
     */
    private $selection_plan_repository;

    /**
     * @var ISummitService
     */
    private $summit_service;

    /**
     * OAuth2SummitSpeakersApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param IEventFeedbackRepository $event_feedback_repository
     * @param IMemberRepository $member_repository
     * @param ISelectionPlanRepository $selection_plan_repository
     * @param ISpeakerService $service
     * @param ISummitService $summit_service
     * @param ISerializerTypeSelector $serializer_type_selector
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        IEventFeedbackRepository $event_feedback_repository,
        IMemberRepository $member_repository,
        ISelectionPlanRepository $selection_plan_repository,
        ISpeakerService $service,
        ISummitService $summit_service,
        ISerializerTypeSelector $serializer_type_selector,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->repository = $summit_repository;
        $this->speaker_repository = $speaker_repository;
        $this->event_repository = $event_repository;
        $this->member_repository = $member_repository;
        $this->event_feedback_repository = $event_feedback_repository;
        $this->selection_plan_repository = $selection_plan_repository;
        $this->service = $service;
        $this->summit_service = $summit_service;
        $this->serializer_type_selector = $serializer_type_selector;
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
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $values = Input::all();

            $rules = array
            (
                'page' => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:10|max:100',
            );

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412($messages);
            }

            // default values
            $page = 1;
            $per_page = 10;

            if (Input::has('page')) {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [

                    'first_name' => ['=@', '=='],
                    'last_name' => ['=@', '=='],
                    'email' => ['=@', '=='],
                    'id' => ['=='],
                ]);
            }

            $order = null;
            if (Input::has('order')) {
                $order = OrderParser::parse(Input::get('order'), [
                    'first_name',
                    'last_name',
                    'id',
                    'email',
                ]);
            }

            $serializer_type = $this->serializer_type_selector->getSerializerType();
            $result = $this->speaker_repository->getSpeakersBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray(Request::input('expand', ''), [], [], ['summit_id' => $summit_id, 'published' => true, 'summit' => $summit], $serializer_type)
            );
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * get all speakers without summit
     * @return mixed
     */
    public function getAll()
    {
        try {

            $values = Input::all();
            $serializer_type = $this->serializer_type_selector->getSerializerType();
            $rules = [
                'page' => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:10|max:100',
            ];

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412($messages);
            }

            // default values
            $page = 1;
            $per_page = 10;

            if (Input::has('page')) {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [

                    'first_name' => ['=@', '=='],
                    'last_name' => ['=@', '=='],
                    'email' => ['=@', '=='],
                    'id' => ['=='],
                ]);
            }

            $order = null;
            if (Input::has('order')) {
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
                $result->toArray(Request::input('expand', ''), [], [], [], $serializer_type)
            );
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $speaker_id
     * @return mixed
     */
    public function getSummitSpeaker($summit_id, $speaker_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $speaker = CheckSpeakerStrategyFactory::build(CheckSpeakerStrategyFactory::Me, $this->resource_server_context)->check($speaker_id, $summit);
            if (is_null($speaker)) return $this->error404();

            $serializer_type = $this->serializer_type_selector->getSerializerType();

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

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getMySpeaker()
    {
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $member = $this->member_repository->getById($current_member_id);
            if (is_null($member))
                return $this->error403();

            $speaker = $this->speaker_repository->getByMember($member);
            if (is_null($speaker)) return $this->error404();

            $serializer_type = $this->serializer_type_selector->getSerializerType();

            return $this->ok
            (
                SerializerRegistry::getInstance()->getSerializer($speaker, $serializer_type)->serialize
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    []
                )
            );

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function createMySpeaker()
    {
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $member = $this->member_repository->getById($current_member_id);
            if (is_null($member))
                return $this->error403();

            // set data from current member ...
            $speaker = $this->service->addSpeaker([
                'member_id' => $member->getIdentifier(),
                'first_name' => $member->getFirstName(),
                'last_name' => $member->getLastName(),
                'bio' => $member->getBio(),
                'twitter' => $member->getTwitterHandle(),
                'irc' => $member->getIrcHandle(),
            ]);

            $serializer_type = $this->serializer_type_selector->getSerializerType();

            return $this->ok
            (
                SerializerRegistry::getInstance()->getSerializer($speaker, $serializer_type)->serialize
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    []
                )
            );

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function updateMySpeaker()
    {
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $member = $this->member_repository->getById($current_member_id);
            if (is_null($member))
                return $this->error403();

            $speaker = $this->speaker_repository->getByMember($member);
            if (is_null($speaker)) return $this->error404();

            return $this->updateSpeaker($speaker->getId());

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $speaker_id
     * @return mixed
     */
    public function getSpeaker($speaker_id)
    {
        try {

            $speaker = $this->speaker_repository->getById($speaker_id);
            if (is_null($speaker)) return $this->error404();

            $serializer_type = $this->serializer_type_selector->getSerializerType();

            return $this->ok
            (
                SerializerRegistry::getInstance()->getSerializer($speaker, $serializer_type)->serialize
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    []
                )
            );

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function addSpeakerBySummit($summit_id)
    {
        try {
            if (!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = [
                'title' => 'required|string|max:100',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'bio' => 'sometimes|string',
                'irc' => 'sometimes|string|max:50',
                'twitter' => 'sometimes|string|max:50',
                'member_id' => 'sometimes|integer',
                'email' => 'sometimes|string|max:50',
                'on_site_phone' => 'sometimes|string|max:50',
                'registered' => 'sometimes|boolean',
                'is_confirmed' => 'sometimes|boolean',
                'checked_in' => 'sometimes|boolean',
                'registration_code' => 'sometimes|string',
                'available_for_bureau' => 'sometimes|boolean',
                'funded_travel' => 'sometimes|boolean',
                'willing_to_travel' => 'sometimes|boolean',
                'willing_to_present_video' => 'sometimes|boolean',
                'org_has_cloud' => 'sometimes|boolean',
                'country' => 'sometimes|country_iso_alpha2_code',
                // collections
                'languages' => 'sometimes|int_array',
                'areas_of_expertise' => 'sometimes|string_array',
                'other_presentation_links' => 'sometimes|string_array',
                'travel_preferences' => 'sometimes|string_array',
                'organizational_roles' => 'sometimes|int_array',
                'other_organizational_rol' => 'sometimes|string|max:255',
                'active_involvements' => 'sometimes|int_array',
            ];

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
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $speaker_id
     * @return mixed
     */
    public function updateSpeakerBySummit($summit_id, $speaker_id)
    {
        try {
            if (!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $speaker = $this->speaker_repository->getById($speaker_id);
            if (is_null($speaker)) return $this->error404();

            $rules = [
                'title' => 'sometimes|string|max:100',
                'first_name' => 'sometimes|string|max:100',
                'last_name' => 'sometimes|string|max:100',
                'bio' => 'sometimes|string',
                'irc' => 'sometimes|string|max:50',
                'twitter' => 'sometimes|string|max:50',
                'member_id' => 'sometimes|integer',
                'email' => 'sometimes|string|max:50',
                'on_site_phone' => 'sometimes|string|max:50',
                'registered' => 'sometimes|boolean',
                'is_confirmed' => 'sometimes|boolean',
                'checked_in' => 'sometimes|boolean',
                'registration_code' => 'sometimes|string',
                'available_for_bureau' => 'sometimes|boolean',
                'funded_travel' => 'sometimes|boolean',
                'willing_to_travel' => 'sometimes|boolean',
                'willing_to_present_video' => 'sometimes|boolean',
                'org_has_cloud' => 'sometimes|boolean',
                'country' => 'sometimes|country_iso_alpha2_code',
                // collections
                'languages' => 'sometimes|int_array',
                'areas_of_expertise' => 'sometimes|string_array',
                'other_presentation_links' => 'sometimes|string_array',
                'travel_preferences' => 'sometimes|string_array',
                'organizational_roles' => 'sometimes|int_array',
                'other_organizational_rol' => 'sometimes|string|max:255',
                'active_involvements' => 'sometimes|int_array',
            ];

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

            $speaker = $this->service->updateSpeakerBySummit($summit, $speaker, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($speaker)->serialize());
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    public function addMySpeakerPhoto(LaravelRequest $request){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $member = $this->member_repository->getById($current_member_id);
            if (is_null($member))
                return $this->error403();

            $speaker = $this->speaker_repository->getByMember($member);
            if (is_null($speaker)) return $this->error404();

           return $this->addSpeakerPhoto($request, $speaker->getId());

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $speaker_id
     * @return mixed
     */
    public function addSpeakerPhoto(LaravelRequest $request, $speaker_id)
    {

        try {

            $file = $request->file('file');
            if (is_null($file)) {
                return $this->error412(array('file param not set!'));
            }

            $res = $this->service->addSpeakerPhoto($speaker_id, $file);

            return !is_null($res) ? $this->created($res->getId()) : $this->error400();
        } catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        } catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412(array($ex2->getMessage()));
        } catch (\HTTP401UnauthorizedException $ex3) {
            Log::warning($ex3);
            return $this->error401();
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $speaker_from_id
     * @param $speaker_to_id
     * @return mixed
     */
    public function merge($speaker_from_id, $speaker_to_id)
    {
        try {
            if (!Request::isJson()) return $this->error400();
            $data = Input::json();

            $speaker_from = $this->speaker_repository->getById($speaker_from_id);
            if (is_null($speaker_from)) return $this->error404();

            $speaker_to = $this->speaker_repository->getById($speaker_to_id);
            if (is_null($speaker_to)) return $this->error404();

            $this->service->merge($speaker_from, $speaker_to, $data->all());

            return $this->updated();
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function addSpeaker()
    {
        try {
            if (!Request::isJson()) return $this->error400();
            $data = Input::json();

            $rules = [
                'title' => 'required|string|max:100',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'bio' => 'sometimes|string',
                'notes' => 'sometimes|string',
                'irc' => 'sometimes|string|max:50',
                'twitter' => 'sometimes|string|max:50',
                'member_id' => 'sometimes|integer',
                'email' => 'sometimes|string|max:50',
                'funded_travel' => 'sometimes|boolean',
                'willing_to_travel' => 'sometimes|boolean',
                'willing_to_present_video' => 'sometimes|boolean',
                'org_has_cloud' => 'sometimes|boolean',
                'available_for_bureau' => 'sometimes|boolean',
                'country' => 'sometimes|country_iso_alpha2_code',
                // collections
                'languages' => 'sometimes|int_array',
                'areas_of_expertise' => 'sometimes|string_array',
                'other_presentation_links' => 'sometimes|string_array',
                'travel_preferences' => 'sometimes|string_array',
                'organizational_roles' => 'sometimes|int_array',
                'other_organizational_rol' => 'sometimes|string|max:255',
                'active_involvements' => 'sometimes|int_array',
            ];

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
                'notes'
            ];

            $speaker = $this->service->addSpeaker(HTMLCleaner::cleanData($data->all(), $fields));

            return $this->created(SerializerRegistry::getInstance()->getSerializer($speaker, SerializerRegistry::SerializerType_Private)->serialize());
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $speaker_id
     * @return mixed
     */
    public function updateSpeaker($speaker_id)
    {
        try {
            if (!Request::isJson()) return $this->error400();
            $data = Input::json();


            $speaker = $this->speaker_repository->getById($speaker_id);
            if (is_null($speaker)) return $this->error404();

            $rules = [
                'title' => 'sometimes|string|max:100',
                'first_name' => 'sometimes|string|max:100',
                'last_name' => 'sometimes|string|max:100',
                'bio' => 'sometimes|string',
                'notes' => 'sometimes|string',
                'irc' => 'sometimes|string|max:50',
                'twitter' => 'sometimes|string|max:50',
                'member_id' => 'sometimes|integer',
                'email' => 'sometimes|string|max:50',
                'available_for_bureau' => 'sometimes|boolean',
                'funded_travel' => 'sometimes|boolean',
                'willing_to_travel' => 'sometimes|boolean',
                'willing_to_present_video' => 'sometimes|boolean',
                'org_has_cloud' => 'sometimes|boolean',
                'country' => 'sometimes|country_iso_alpha2_code',
                // collections
                'languages' => 'sometimes|int_array',
                'areas_of_expertise' => 'sometimes|string_array',
                'other_presentation_links' => 'sometimes|string_array',
                'travel_preferences' => 'sometimes|string_array',
                'organizational_roles' => 'sometimes|int_array',
                'other_organizational_rol' => 'sometimes|string|max:255',
                'active_involvements' => 'sometimes|int_array',
            ];

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
                'notes',
            ];

            $speaker = $this->service->updateSpeaker($speaker, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($speaker, SerializerRegistry::SerializerType_Private)->serialize());
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(['message' => $ex2->getMessage()]);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $speaker_id
     * @return mixed
     */
    public function deleteSpeaker($speaker_id)
    {
        try {

            $speaker = $this->speaker_repository->getById($speaker_id);
            if (is_null($speaker)) return $this->error404();
            $this->service->deleteSpeaker($speaker_id);
            return $this->deleted();
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $role
     * @param $selection_plan_id
     * @return mixed
     */
    public function getMySpeakerPresentationsByRoleAndBySelectionPlan($role, $selection_plan_id)
    {
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $member = $this->member_repository->getById($current_member_id);
            if (is_null($member))
                return $this->error403();

            $speaker = $this->speaker_repository->getByMember($member);
            if (is_null($speaker))
                return $this->error403();

            $selection_plan = $this->selection_plan_repository->getById($selection_plan_id);
            if (is_null($selection_plan))
                return $this->error404(['message' => 'missing selection plan']);

            switch ($role) {
                case 'creator':
                    $role = PresentationSpeaker::ROLE_CREATOR;
                    break;
                case 'speaker':
                    $role = PresentationSpeaker::ROLE_SPEAKER;
                    break;
                case 'moderator':
                    $role = PresentationSpeaker::ROLE_MODERATOR;
                    break;
            }
            $presentations = $speaker->getPresentationsBySelectionPlanAndRole($selection_plan, $role);

            $response = new PagingResponse
            (
                count($presentations),
                count($presentations),
                1,
                1,
                $presentations
            );

            return $this->ok($response->toArray($expand = Input::get('expand', '')));
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $presentation_id
     * @param $speaker_id
     * @return mixed
     */
    public function addSpeakerToMyPresentation($presentation_id, $speaker_id){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $this->summit_service->addSpeaker2Presentation($current_member_id, $speaker_id, $presentation_id);

            return $this->updated();

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $presentation_id
     * @param $speaker_id
     * @return mixed
     */
    public function addModeratorToMyPresentation($presentation_id, $speaker_id){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $this->summit_service->addModerator2Presentation($current_member_id, $speaker_id, $presentation_id);

            return $this->updated();

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $presentation_id
     * @param $speaker_id
     * @return mixed
     */
    public function removeSpeakerFromMyPresentation($presentation_id, $speaker_id){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $this->summit_service->removeSpeakerFromPresentation($current_member_id, $speaker_id, $presentation_id);

            return $this->deleted();

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $presentation_id
     * @param $speaker_id
     * @return mixed
     */
    public function removeModeratorFromMyPresentation($presentation_id, $speaker_id){
        try {
            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id))
                return $this->error403();

            $this->summit_service->removeModeratorFromPresentation($current_member_id, $speaker_id, $presentation_id);

            return $this->deleted();

        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412(array($ex1->getMessage()));
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

}