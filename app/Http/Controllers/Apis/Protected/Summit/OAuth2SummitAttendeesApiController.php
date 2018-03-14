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
use App\Services\Model\IAttendeeService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitAttendeeRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\Filter;
use utils\FilterParser;
use utils\OrderParser;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use utils\PagingInfo;
/**
 * Class OAuth2SummitAttendeesApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitAttendeesApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitService
     */
    private $summit_service;

    /**
     * @var IAttendeeService
     */
    private $attendee_service;

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
     * @var ISummitAttendeeRepository
     */
    private $attendee_repository;

    /**
     * OAuth2SummitAttendeesApiController constructor.
     * @param ISummitAttendeeRepository $attendee_repository
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param IEventFeedbackRepository $event_feedback_repository
     * @param ISummitService $summit_service
     * @param IAttendeeService $attendee_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitAttendeeRepository $attendee_repository,
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        IEventFeedbackRepository $event_feedback_repository,
        ISummitService $summit_service,
        IAttendeeService $attendee_service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->attendee_repository       = $attendee_repository;
        $this->repository                = $summit_repository;
        $this->speaker_repository        = $speaker_repository;
        $this->event_repository          = $event_repository;
        $this->event_feedback_repository = $event_feedback_repository;
        $this->summit_service            = $summit_service;
        $this->attendee_service          = $attendee_service;
    }

    /**
     *  Attendees endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getOwnAttendee($summit_id){
        $expand = Request::input('expand', '');

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $type     = CheckAttendeeStrategyFactory::Me;
            $attendee = CheckAttendeeStrategyFactory::build($type, $this->resource_server_context)->check('me', $summit);
            if(is_null($attendee)) return $this->error404();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($attendee)->serialize($expand));
        }
        catch (\HTTP401UnauthorizedException $ex1) {
            Log::warning($ex1);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $attendee_id
     * @return mixed
     */
    public function getAttendee($summit_id, $attendee_id)
    {
        $expand = Request::input('expand', '');

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = $this->attendee_repository->getById($attendee_id);
            if(is_null($attendee)) return $this->error404();

            return $this->ok
            (
                SerializerRegistry::getInstance()->getSerializer
                (
                    $attendee,
                    SerializerRegistry::SerializerType_Private
                )->serialize
                (
                    $expand,
                    [],
                    [],
                    [ 'serializer_type' => SerializerRegistry::SerializerType_Private ]
                ));
        }
        catch (\HTTP401UnauthorizedException $ex1) {
            Log::warning($ex1);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $attendee_id
     * @return mixed
     */
    public function getAttendeeSchedule($summit_id, $attendee_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee =  CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if(is_null($attendee)) return $this->error404();

            $schedule = [];
            foreach ($attendee->getSchedule() as $attendee_schedule)
            {
                if(!$summit->isEventOnSchedule($attendee_schedule->getEvent()->getId())) continue;
                $schedule[] = SerializerRegistry::getInstance()->getSerializer($attendee_schedule)->serialize();
            }

            return $this->ok($schedule);
        }
        catch (\HTTP401UnauthorizedException $ex1)
        {
            Log::warning($ex1);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $attendee_id
     * @param $event_id
     * @return mixed
     */
    public function addEventToAttendeeSchedule($summit_id, $attendee_id, $event_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $this->summit_service->addEventToMemberSchedule($summit, $attendee->getMember(), intval($event_id));

            return $this->created();
        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array( $ex1->getMessage()));
        }
        catch (EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        }
        catch(\HTTP401UnauthorizedException $ex3)
        {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $attendee_id
     * @param $event_id
     * @return mixed
     */
    public function removeEventFromAttendeeSchedule($summit_id, $attendee_id, $event_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $this->summit_service->removeEventFromMemberSchedule($summit, $attendee->getMember(), intval($event_id));

            return $this->deleted();

        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array( $ex1->getMessage()));
        }
        catch (EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        }
        catch(\HTTP401UnauthorizedException $ex3)
        {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $attendee_id
     * @param $event_id
     * @return mixed
     */
    public function deleteEventRSVP($summit_id, $attendee_id, $event_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $this->summit_service->unRSVPEvent($summit, $attendee->getMember(), $event_id);

            return $this->deleted();

        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array( $ex1->getMessage()));
        }
        catch (EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
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
     * @param $summit_id
     * @return mixed
     */
    public function getAttendeesBySummit($summit_id){

        $values = Input::all();
        $rules  = [

            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        ];

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

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
                $filter = FilterParser::parse(Input::get('filter'), [

                    'first_name'           => ['=@', '=='],
                    'last_name'            => ['=@', '=='],
                    'email'                => ['=@', '=='],
                    'external_order_id'    => ['=@', '=='],
                    'external_attendee_id' => ['=@', '=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'first_name',
                    'last_name',
                    'id',
                    'external_order_id',
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            $data      = $this->attendee_repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $data->toArray
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    [ 'serializer_type' => SerializerRegistry::SerializerType_Private ]
                )
            );
        }
        catch (ValidationException $ex1)
        {
            Log::warning($ex1);
            return $this->error412(array( $ex1->getMessage()));
        }
        catch (EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
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
     * @param int $summit_id
     * @return mixed
     */
    public function addAttendee($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = [

                'member_id'                   => 'required|integer',
                'shared_contact_info'         => 'sometimes|boolean',
                'summit_hall_checked_in'      => 'sometimes|boolean',
                'summit_hall_checked_in_date' => 'sometimes|date_format:U',
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

            $attendee = $this->attendee_service->addAttendee($summit, $data->all());

            return $this->created(SerializerRegistry::getInstance()->getSerializer($attendee)->serialize());
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
     * @param $attendee_id
     * @return mixed
     */
    public function deleteAttendee($summit_id, $attendee_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = $this->attendee_repository->getById($attendee_id);
            if(is_null($attendee)) return $this->error404();

            $this->attendee_service->deleteAttendee($summit, $attendee->getIdentifier());

            return $this->deleted();

        }
        catch (\HTTP401UnauthorizedException $ex1) {
            Log::warning($ex1);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param int $summit_id
     * @param int $attendee_id
     * @return mixed
     */
    public function updateAttendee($summit_id, $attendee_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = $this->attendee_repository->getById($attendee_id);
            if(is_null($attendee)) return $this->error404();

            $rules = [
                'member_id'                   => 'required|integer',
                'shared_contact_info'         => 'sometimes|boolean',
                'summit_hall_checked_in'      => 'sometimes|boolean',
                'summit_hall_checked_in_date' => 'sometimes|date_format:U',
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

            $attendee = $this->attendee_service->updateAttendee($summit, $attendee_id, $data->all());

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($attendee)->serialize());
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
     * @param $attendee_id
     * @return mixed
     */
    public function addAttendeeTicket($summit_id, $attendee_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = $this->attendee_repository->getById($attendee_id);
            if(is_null($attendee)) return $this->error404();

            $rules = [
                'ticket_type_id'       => 'required|integer',
                'external_order_id'    => 'required|string',
                'external_attendee_id' => 'required|string',
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

            $ticket = $this->attendee_service->addAttendeeTicket($attendee, $data->all());

            return $this->created(SerializerRegistry::getInstance()->getSerializer($ticket)->serialize());
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
     * @param $attendee_id
     * @param $ticket_id
     * @return mixed
     */
    public function deleteAttendeeTicket($summit_id, $attendee_id, $ticket_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = $this->attendee_repository->getById($attendee_id);
            if(is_null($attendee)) return $this->error404();

            $ticket = $this->attendee_service->deleteAttendeeTicket($attendee, $ticket_id);

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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}