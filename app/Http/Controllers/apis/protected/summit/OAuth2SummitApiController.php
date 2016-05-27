<?php namespace App\Http\Controllers;

use Exception;
use libs\utils\HTMLCleaner;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\ISummitEventRepository;
use models\summit\Presentation;
use Illuminate\Http\Request as LaravelRequest;
use models\oauth2\IResourceServerContext;
use models\summit\ISpeakerRepository;
use models\summit\ISummitRepository;
use services\model\ISummitService;
use utils\OrderParser;
use utils\PagingInfo;
use utils\FilterParser;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use utils\PagingResponse;

/**
 * Copyright 2015 OpenStack Foundation
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
class OAuth2SummitApiController extends OAuth2ProtectedController
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


    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        ISummitService $service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);

        $this->repository          = $summit_repository;
        $this->speaker_repository  = $speaker_repository;
        $this->event_repository    = $event_repository;
        $this->service             = $service;
    }

    public function getSummits()
    {
        try {
            $summits = $this->repository->getAll();
            return $this->ok($summits);
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
    public function getSummit($summit_id)
    {
        $expand = Request::input('expand', '');
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            return $this->ok($summit, $expand);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     *  Attendees endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAttendees($summit_id)
    {
        try {

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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            // default values
            $page     = 1;
            $per_page = 5;

            if (Input::has('page')) {
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;
            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), array
                (
                    'first_name' => array('=@', '=='),
                    'last_name'  => array('=@', '=='),
                    'email'      => array('=@', '=='),
                ));
            }

            $order = null;
            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'first_name',
                    'last_name',
                ));
            }

            list($total, $per_page, $current_page, $last_page, $items) = $summit->attendees($page, $per_page, $filter, $order);

            return $this->ok
            (
                array
                (
                    'total'        => $total,
                    'per_page'     => $per_page,
                    'current_page' => $current_page,
                    'last_page'    => $last_page,
                    'data'         => $items,
                )
            );

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
     * @return mixed
     */
    public function getAttendee($summit_id, $attendee_id)
    {
        $expand = Request::input('expand', '');

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if(is_null($attendee)) return $this->error404();

            $data    = $attendee->toArray();
            $speaker = $summit->getSpeakerByMemberId(intval($data['member_id']));

            if (!is_null($speaker)) {
                $data['speaker_id'] = intval($speaker->ID);
            }

            if (!empty($expand)) {
                $expand = explode(',', $expand);
                foreach ($expand as $relation) {
                    switch (trim($relation)) {
                        case 'schedule': {
                            unset($data['schedule']);
                            $schedule = array();
                            foreach ($attendee->schedule() as $event) {
                                $event->setFromAttendee();
                                array_push($schedule, $event->toArray());
                            }
                            $data['schedule'] = $schedule;
                        }
                        break;
                        case 'ticket_type': {
                            unset($data['tickets']);
                            $tickets = array();
                            foreach($attendee->tickets() as $t)
                            {
                                array_push($tickets, $t->ticket_type()->toArray());
                            }
                            $data['tickets'] = $tickets;
                        }
                        break;
                        case 'speaker': {
                            if (!is_null($speaker))
                            {
                                unset($data['speaker_id']);
                                $data['speaker'] = $speaker->toArray();
                            }
                        }
                        break;
                        case 'feedback': {
                            $feedback = array();
                            foreach ($attendee->emitted_feedback() as $f) {
                                array_push($feedback, $f->toArray());
                            }
                            $data['feedback'] = $feedback;
                        }
                        break;
                    }
                }
            }

            return $this->ok($data);
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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee =  CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if(is_null($attendee)) return $this->error404();

            $schedule = array();
            foreach ($attendee->schedule() as $event) {
                $event->setFromAttendee();
                array_push($schedule, $event->toArray());
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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $res = $this->service->addEventToAttendeeSchedule($summit, $attendee, intval($event_id));

            return $res ? $this->created() : $this->error400();
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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $res = $this->service->removeEventFromAttendeeSchedule($summit, $attendee, intval($event_id));

            return $res ? $this->deleted() : $this->error400();

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
    public function checkingAttendeeOnEvent($summit_id, $attendee_id, $event_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $res = $this->service->checkInAttendeeOnEvent($summit, $attendee, intval($event_id));

            return $res ? $this->updated() : $this->error400();
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
     *  Speakers endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSpeakers($summit_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
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
                $filter = FilterParser::parse(Input::get('filter'), array
                (
                    'first_name' => array('=@', '=='),
                    'last_name'  => array('=@', '=='),
                    'email'      => array('=@', '=='),
                ));
            }


            $order = null;
            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'first_name',
                    'last_name',
                ));
            }

            $result = $this->speaker_repository->getSpeakersBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray()
            );
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
        $expand = Request::input('expand', '');

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $speaker = CheckSpeakerStrategyFactory::build(CheckSpeakerStrategyFactory::Me, $this->resource_server_context)->check($speaker_id, $summit);
            if (is_null($speaker)) return $this->error404();

            $data = $speaker->toArray($summit->ID);

            if (!empty($expand)) {
                $expand = explode(',', $expand);
                foreach ($expand as $relation) {
                    switch (trim($relation)) {
                        case 'presentations': {
                            $presentations = array();
                            unset($data['presentations']);
                            foreach ($speaker->presentations($summit->ID) as $event) {
                                $event->setFromSpeaker();
                                array_push($presentations, $event->toArray());
                            }
                            $data['presentations'] = $presentations;
                        }
                            break;
                    }
                }
            }

            return $this->ok($data);

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
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
            $strategy = new RetrieveAllSummitEventsBySummitStrategy($this->repository);
            return $this->ok($strategy->getEvents(array('summit_id' => $summit_id))->toArray());
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
            $strategy = new RetrievePublishedSummitEventsBySummitStrategy($this->repository);
            return $this->ok($strategy->getEvents(array('summit_id' => $summit_id))->toArray());
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

    public function getAllEvents()
    {
        try
        {
            $strategy = new RetrieveAllSummitEventsStrategy($this->event_repository);
            return $this->ok($strategy->getEvents()->toArray());
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

    public function getAllScheduledEvents()
    {
        try
        {
            $strategy = new RetrieveAllPublishedSummitEventsStrategy($this->event_repository);
            return $this->ok($strategy->getEvents()->toArray());
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
        $data      = $event->toArray($fields, $relations);

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'feedback': {
                        $feedback = array();
                        list($total, $per_page, $current_page, $last_page, $items) = $event->feedback(1, PHP_INT_MAX);
                        foreach ($items as $f) {
                            array_push($feedback, $f->toArray());
                        }
                        $data['feedback'] = $feedback;
                    }
                    break;
                    case 'speakers':{
                        if($event instanceof Presentation){
                            unset($data['speakers']);
                            $speakers = array();
                            foreach($event->speakers() as $speaker)
                            {
                                array_push($speakers, $speaker->toArray());
                            }
                            $data['speakers'] = $speakers;
                        }
                    }
                    break;
                    case 'location': {
                        $location         = $event->getLocation();
                        $data['location'] = $location->toArray();
                        unset($data['location_id']);
                    }
                    break;
                }
            }
        }
        return $data;
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

            $data = $this->_getSummitEvent($summit_id, $event_id, $expand, $fields, $relations, false);
            return $this->ok($data);
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

            $data = $this->_getSummitEvent($summit_id, $event_id, $expand, $fields, $relations, true);
            return $this->ok($data);
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
                'title'           => 'required|string|max:300',
                'description'     => 'required|string',
                'location_id'     => 'sometimes|required|integer',
                'start_date'      => 'sometimes|required|date_format:U',
                'end_date'        => 'sometimes|required_with:start_date|date_format:U|after:start_date',
                'allow_feedback'  => 'sometimes|required|boolean',
                'type_id'         => 'required|integer',
                'summit_types_id' => 'required|int_array',
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
                'description'
            );

            $event = $this->service->addEvent($summit, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->created($event);
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
                'title'           => 'sometimes|required|string|max:300',
                'description'     => 'sometimes|required|string',
                'location_id'     => 'sometimes|required|integer',
                'start_date'      => 'sometimes|required|date_format:U',
                'end_date'        => 'sometimes|required_with:start_date|date_format:U|after:start_date',
                'allow_feedback'  => 'sometimes|required|boolean',
                'type_id'         => 'sometimes|required|integer',
                'summit_types_id' => 'sometimes|required|int_array',
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
                'description'
            );

            $event = $this->service->updateEvent($summit, $event_id, HTMLCleaner::cleanData($data->all(), $fields));

            return $this->ok($event);

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

            $expand = Request::input('expand', '');

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

                $filter = FilterParser::parse(array('owner_id' => $member_id), array
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

            list($total, $per_page, $current_page, $last_page, $feedback) = $event->feedback($page, $per_page, $filter, $order);

            if (!empty($expand))
            {
                foreach (explode(',', $expand) as $relation)
                {
                    switch (trim($relation)) {
                        case 'owner':
                        {
                            $res = array();
                            foreach($feedback as $f)
                            {
                                array_push($res, $f->toArray(true));
                            }
                            $feedback = $res;
                        }
                        break;
                    }
                }
            }

            return $this->ok
            (
                array
                (
                    'total'        => $total,
                    'per_page'     => $per_page,
                    'current_page' => $current_page,
                    'last_page'    => $last_page,
                    'data'         => $feedback,
                )
            );

        } catch (Exception $ex) {
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

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $data['attendee_id'] = intval($attendee->ID);

            $res  = $this->service->addEventFeedback
            (
                $summit,
                $event,
                $data
            );

            return !is_null($res) ? $this->created($res->ID) : $this->error400();
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
     * @param $summit_id
     * @return mixed
     */
    public function getSummitEntityEvents($summit_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $last_event_id = Request::input('last_event_id', null);
            $from_date     = Request::input('from_date', null);
            $limit         = Request::input('limit', 25);

            $rules = array
            (
                'last_event_id' => 'sometimes|required|integer',
                'from_date'     => 'sometimes|required|integer',
                'limit'         => 'sometimes|required|integer',
            );

            $data = array();

            if (!is_null($last_event_id))
            {
                $data['last_event_id'] = $last_event_id;
            }

            if (!is_null($from_date))
            {
                $data['from_date'] = $from_date;
            }

            if(!is_null($limit)){
                $data['limit'] = $limit;
            }

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data, $rules);

            if ($validation->fails())
            {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            if (!is_null($from_date))
            {
                $from_date = new \DateTime("@$from_date");
            }

            list($last_event_id, $last_event_date, $list) = $this->service->getSummitEntityEvents
            (
                $summit,
                $this->resource_server_context->getCurrentUserExternalId(),
                $from_date,
                intval($last_event_id),
                intval($limit)
            );

            return $this->ok
            (
            //todo: send this new response once that testing is done!
            /*array
            (
                'events'          => $list,
                'last_event_id'   => $last_event_id,
                'last_event_date' => $last_event_date->getTimestamp()
            )*/
                $list
            );
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    //venues

    public function getLocations($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->locations() as $location) {
                array_push($locations, $location->toArray());
            }

            return $this->ok($locations);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function getLocation($summit_id, $location_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $location = $summit->getLocation($location_id);
            if (is_null($location)) {
                return $this->error404();
            }
            return $this->ok($location);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param string $summit_id
     * @param int $location_id
     * @param bool $published
     * @return PagingResponse
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    private function _getLocationEvents($summit_id, $location_id, $published = true)
    {
        $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
        if (is_null($summit))
            throw new EntityNotFoundException;

        $location = $summit->getLocation($location_id);
        if (is_null($location))
            throw new EntityNotFoundException;

        $values = Input::all();

        $rules = array
        (
            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        );

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
                'title'          => array('=@', '=='),
                'speaker'        => array('=@', '=='),
                'tags'           => array('=@', '=='),
                'start_date'     => array('>', '<', '<=', '>=', '=='),
                'end_date'       => array('>', '<', '<=', '>=', '=='),
                'summit_type_id' => array('=='),
                'event_type_id'  => array('=='),
                'track_id'       => array('=='),
            ));
        }

        list($total, $per_page, $current_page, $last_page, $events) = $location->events
        (
            $page, $per_page, $filter , $published
        );

        return new PagingResponse
        (
            $total,
            $per_page,
            $current_page,
            $last_page,
            $events
        );
    }

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function getLocationEvents($summit_id, $location_id)
    {
        try {

            return $this->ok($this->_getLocationEvents($summit_id, $location_id, false)->toArray());
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function getLocationPublishedEvents($summit_id, $location_id)
    {
        try {

            return $this->ok($this->_getLocationEvents($summit_id, $location_id, true)->toArray());
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
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
    public function getEventTypes($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //event types
            $list = array();
            foreach ($summit->event_types() as $et) {
                array_push($list, $et->toArray());
            }

            return $this->ok($list);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSummitTypes($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //summit types
            $list = array();
            foreach ($summit->summit_types() as $st) {
                array_push($list, $st->toArray());
            }
            return $this->ok($list);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }


    public function getExternalOrder($summit_id, $external_order_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $order = $this->service->getExternalOrder($summit, $external_order_id);
            return $this->ok($order);
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404(array('message' => $ex1->getMessage()));
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    public function confirmExternalOrderAttendee($summit_id, $external_order_id, $external_attendee_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($member_id)) {
                throw new \HTTP401UnauthorizedException;
            }
            $attendee = $this->service->confirmExternalOrderAttendee($summit, $member_id, $external_order_id, $external_attendee_id);
            return $this->ok($attendee);
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404(array('message' => $ex1->getMessage()));
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch (\HTTP401UnauthorizedException $ex3) {
            Log::warning($ex3);
            return $this->error401();
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

}