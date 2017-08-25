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
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use libs\utils\HTMLCleaner;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\FilterParser;
use utils\FilterParserException;
use utils\OrderParser;
use utils\PagingInfo;

/**
 * Class OAuth2SummitEventsApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitEventsApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitService
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


    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        IEventFeedbackRepository $event_feedback_repository,
        ISummitService $service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->repository                = $summit_repository;
        $this->speaker_repository        = $speaker_repository;
        $this->event_repository          = $event_repository;
        $this->event_feedback_repository = $event_feedback_repository;
        $this->service                   = $service;
    }

    /**
     *  Events endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getEvents($summit_id)
    {
        try
        {
            $strategy = new RetrieveAllSummitEventsBySummitStrategy($this->repository, $this->event_repository);
            $response = $strategy->getEvents(['summit_id' => $summit_id]);
            return $this->ok($response->toArray(Request::input('expand', '')));
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getScheduledEvents($summit_id)
    {
        try
        {
            $strategy = new RetrievePublishedSummitEventsBySummitStrategy($this->repository, $this->event_repository);
            $response = $strategy->getEvents(['summit_id' => $summit_id]);
            return $this->ok($response->toArray(Request::input('expand', '')));
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getAllEvents()
    {
        try
        {
            $strategy = new RetrieveAllSummitEventsStrategy($this->event_repository);
            $response = $strategy->getEvents();
            return $this->ok($response->toArray(Request::input('expand', '')));
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @return mixed
     */
    public function getAllScheduledEvents()
    {
        try
        {
            $strategy = new RetrieveAllPublishedSummitEventsStrategy($this->event_repository);
            $response = $strategy->getEvents();
            return $this->ok($response->toArray(Request::input('expand', '')));
        }
        catch (EntityNotFoundException $ex1)
        {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2)
        {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $event_id
     * @param string $expand
     * @param string $fields
     * @param string $relations
     * @param bool $published
     * @return array
     * @throws EntityNotFoundException
     */
    private function _getSummitEvent($summit_id, $event_id, $expand = '', $fields = '', $relations = '', $published = true)
    {
        $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
        if (is_null($summit)) throw new EntityNotFoundException;

        $event =  $published ? $summit->getScheduleEvent(intval($event_id)) : $summit->getEvent(intval($event_id));

        if (is_null($event)) throw new EntityNotFoundException;
        $relations = !empty($relations) ? explode(',', $relations) : array();
        $fields    = !empty($fields) ? explode(',', $fields) : array();

        return SerializerRegistry::getInstance()->getSerializer($event)->serialize($expand, $fields, $relations);
    }
    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function getEvent($summit_id, $event_id)
    {
        try {

            $expand    = Request::input('expand', '');
            $fields    = Request::input('fields', '');
            $relations = Request::input('relations', '');

            return $this->ok($this->_getSummitEvent($summit_id, $event_id, $expand, $fields, $relations, false));
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function getScheduledEvent($summit_id, $event_id)
    {
        try {

            $expand    = Request::input('expand', '');
            $fields    = Request::input('fields', '');
            $relations = Request::input('relations', '');

            return $this->ok($this->_getSummitEvent($summit_id, $event_id, $expand, $fields, $relations, true));
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function addEvent($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $rules = array
            (
                'title'           => 'required|string|max:100',
                'description'     => 'required|string',
                'social_summary'  => 'sometimes|string|max:100',
                'location_id'     => 'sometimes|required|integer',
                'start_date'      => 'sometimes|required|date_format:U',
                'end_date'        => 'sometimes|required_with:start_date|date_format:U|after:start_date',
                'allow_feedback'  => 'sometimes|required|boolean',
                'type_id'         => 'required|integer',
                'track_id'        => 'required|integer',
                'tags'            => 'sometimes|required|string_array',
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
                'title',
                'description',
                'social_summary',
            );

            $event = $this->service->addEvent($summit, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->created(SerializerRegistry::getInstance()->getSerializer($event)->serialize());
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

    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function updateEvent($summit_id, $event_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $rules = array
            (
                'title'           => 'sometimes|required|string|max:100',
                'description'     => 'sometimes|required|string',
                'social_summary'  => 'sometimes|string|max:100',
                'location_id'     => 'sometimes|required|integer',
                'start_date'      => 'sometimes|required|date_format:U',
                'end_date'        => 'sometimes|required_with:start_date|date_format:U|after:start_date',
                'allow_feedback'  => 'sometimes|required|boolean',
                'type_id'         => 'sometimes|required|integer',
                'track_id'        => 'sometimes|required|integer',
                'tags'            => 'sometimes|required|string_array',
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
                'title',
                'description',
                'social_summary',
            );

            $event = $this->service->updateEvent($summit, $event_id, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($event)->serialize());

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

    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function publishEvent($summit_id, $event_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $rules = array
            (
                'location_id'     => 'sometimes|required|integer',
                'start_date'      => 'sometimes|required|date_format:U',
                'end_date'        => 'sometimes|required_with:start_date|date_format:U|after:start_date',
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

            $this->service->publishEvent($summit, $event_id, $data->all());

            return $this->updated();
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

    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function unPublishEvent($summit_id, $event_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            if(!Request::isJson()) return $this->error403();


            $this->service->unPublishEvent($summit, $event_id);

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

    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function deleteEvent($summit_id, $event_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->service->deleteEvent($summit, $event_id);

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

    /** Feedback endpoints  */

    /**
     * @param $summit_id
     * @param $event_id
     * @param $attendee_id
     * @return mixed
     */
    public function getEventFeedback($summit_id, $event_id, $attendee_id = null)
    {

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $values = Input::all();

            $rules = array
            (
                'page'     => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:5|max:100',
            );

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412($messages);
            }

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $filter  = null;
            if (!is_null($attendee_id)) // add filter by attendee, this case me
            {
                if($attendee_id !== 'me') return $this->error403();
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) return $this->error404();

                $filter = FilterParser::parse('owner_id=='.$member_id, array
                (
                    'owner_id'   => array('=='),
                ));
            }

            // default values
            $page     = 1;
            $per_page = 5;

            if (Input::has('page'))
            {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $order = null;
            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'created_date',
                    'owner_id',
                    'rate',
                    'id',
                ));
            }

            $response = $this->event_feedback_repository->getByEvent($event, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok($response->toArray(Request::input('expand', '')));

        }
        catch(FilterParserException $ex1){
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function addEventFeedback(LaravelRequest $request, $summit_id, $event_id)
    {
        try {
            if (!$request->isJson()) {
                return $this->error412(array('invalid content type!'));
            }

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            if(!Request::isJson()) return $this->error403();

            $data = Input::json();

            $rules = array
            (
                'rate'        => 'required|integer|digits_between:0,10',
                'note'        => 'required|max:500',
                'attendee_id' => 'required'
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

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $data         = $data->all();
            $attendee_id  = $data['attendee_id'];

            $attendee = CheckAttendeeStrategyFactory::build
            (
                CheckAttendeeStrategyFactory::Own,
                $this->resource_server_context
            )->check($attendee_id, $summit);

            if (is_null($attendee)) return $this->error404();

            $data['attendee_id'] = intval($attendee->getId());

            $res  = $this->service->addEventFeedback
            (
                $summit,
                $event,
                $data
            );

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

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function addEventFeedbackByMember(LaravelRequest $request, $summit_id, $event_id)
    {
        try {

            list($summit, $event, $data) = $this->validateAndGetFeedbackData($request, $summit_id, $event_id);

            $res  = $this->service->addEventFeedback
            (
                $summit,
                $event,
                $data
            );

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

    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function updateEventFeedbackByMember(LaravelRequest $request, $summit_id, $event_id)
    {
        try {

            list($summit, $event, $data) = $this->validateAndGetFeedbackData($request, $summit_id, $event_id);
            $res  = $this->service->updateEventFeedback
            (
                $summit,
                $event,
                $data
            );

            return !is_null($res) ? $this->updated($res->getId()) : $this->error400();
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

    private function validateAndGetFeedbackData(LaravelRequest $request, $summit_id, $event_id){
        if (!$request->isJson()) {
            return $this->error412(array('invalid content type!'));
        }

        $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
        if (is_null($summit)) return $this->error404();
        if(!Request::isJson()) return $this->error403();

        $data = Input::json();

        $rules = array
        (
            'rate'        => 'required|integer|digits_between:0,5',
            'note'        => 'max:500',
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

        $event = $summit->getScheduleEvent(intval($event_id));

        if (is_null($event)) {
            return $this->error404();
        }

        $data      = $data->all();
        $member_id = $this->resource_server_context->getCurrentUserExternalId();

        if (is_null($member_id)) return $this->error403();

        $data['member_id'] = intval($member_id);

        return [$summit, $event, $data];
    }

}