<?php namespace App\Http\Controllers;

use Exception;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use utils\FilterParser;
use Illuminate\Http\Request as LaravelRequest;
use Input;
use Log;
use models\oauth2\IResourceServerContext;
use models\summit\ISpeakerRepository;
use models\summit\ISummitRepository;
use Request;
use services\model\ISummitService;
use utils\OrderParser;
use utils\PagingInfo;
use Validator;

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

    public function __construct
    (
        ISummitRepository $summit_repository,
        ISpeakerRepository $speaker_repository,
        ISummitService $service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);

        $this->repository         = $summit_repository;
        $this->speaker_repository = $speaker_repository;
        $this->service            = $service;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSummit($summit_id)
    {
        $expand = Request::input('expand', '');

        try {
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }

            $data = $summit->toArray();
            // summit types
            $summit_types = array();
            foreach ($summit->summit_types() as $type) {
                array_push($summit_types, $type->toArray());
            }
            $data['summit_types'] = $summit_types;
            // tickets
            $ticket_types = array();
            foreach ($summit->ticket_types() as $ticket) {
                array_push($ticket_types, $ticket->toArray());
            }
            $data['ticket_types'] = $ticket_types;
            //locations
            $locations = array();
            foreach ($summit->locations() as $location) {
                array_push($locations, $location->toArray());
            }
            $data['locations'] = $locations;

            if (!empty($expand)) {
                $expand = explode(',', $expand);
                foreach ($expand as $relation) {
                    switch (trim($relation)) {
                        case 'attendees': {
                            $attendees = array();
                            list($total, $per_page, $current_page, $last_page, $items) = $summit->attendees(1,
                                PHP_INT_MAX);
                            foreach ($items as $attendee) {
                                array_push($attendees, $attendee->toArray());
                            }
                            $data['attendees'] = $attendees;
                        }
                            break;
                        case 'schedule': {
                            $event_types = array();
                            foreach ($summit->event_types() as $event_type) {
                                array_push($event_types, $event_type->toArray());
                            }
                            $data['event_types'] = $event_types;

                            $sponsors = array();
                            foreach ($summit->sponsors() as $company) {
                                array_push($sponsors, $company->toArray());
                            }
                            $data['sponsors'] = $sponsors;

                            $speakers = array();
                            $res = $this->speaker_repository->getSpeakersBySummit($summit, new PagingInfo(1 , PHP_INT_MAX));

                            foreach ($res->getItems() as $speaker) {
                                array_push($speakers, $speaker->toArray());
                            }
                            $data['speakers'] = $speakers;

                            $presentation_categories = array();
                            foreach ($summit->presentation_categories() as $cat) {
                                array_push($presentation_categories, $cat->toArray());
                            }
                            $data['tracks'] = $presentation_categories;

                            $schedule = array();
                            list($total, $per_page, $current_page, $last_page, $items) = $summit->schedule(1,
                                PHP_INT_MAX);
                            foreach ($items as $event) {
                                array_push($schedule, $event->toArray());
                            }
                            $data['schedule'] = $schedule;

                        }
                            break;
                    }
                }
            }
            $data['timestamp'] = time();
            return $this->ok($data);
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

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
            if ($attendee_id === 'me') {
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) {
                    return $this->error404();
                }
                $attendee = $summit->getAttendeeByMemberId($member_id);
            } else {
                $attendee = $summit->getAttendeeById(intval($attendee_id));
            }

            if (is_null($attendee)) {
                return $this->error404();
            }

            $data = $attendee->toArray();
            $speaker = $summit->getSpeakerByMemberId(intval($data['member_id']));
            if (!is_null($speaker)) {
                $data['speaker_id'] = $speaker->ID;
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
                            unset($data['ticket_type_id']);
                            $data['ticket_type'] = $attendee->ticket_type()->toArray();
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
        } catch (Exception $ex) {
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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
            if ($attendee_id === 'me') {
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) {
                    return $this->error404();
                }
                $attendee = $summit->getAttendeeByMemberId($member_id);
            } else {
                $attendee = $summit->getAttendeeById(intval($attendee_id));
            }

            if (is_null($attendee)) {
                return $this->error404();
            }

            $schedule = array();
            foreach ($attendee->schedule() as $event) {
                $event->setFromAttendee();
                array_push($schedule, $event->toArray());
            }

            return $this->ok($schedule);
        } catch (Exception $ex) {
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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
            if ($attendee_id === 'me') {
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) {
                    return $this->error404();
                }
                $attendee = $summit->getAttendeeByMemberId($member_id);
            } else {
                $attendee = $summit->getAttendeeById(intval($attendee_id));
            }

            if (is_null($attendee)) {
                return $this->error404();
            }

            $res = $this->service->addEventToAttendeeSchedule($summit, $attendee, intval($event_id));

            return $res ? $this->created() : $this->error400();
        } catch (Exception $ex) {
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
    public function removeEventToAttendeeSchedule($summit_id, $attendee_id, $event_id)
    {
        try {
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
            if ($attendee_id === 'me') {
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) {
                    return $this->error404();
                }
                $attendee = $summit->getAttendeeByMemberId($member_id);
            } else {
                $attendee = $summit->getAttendeeById(intval($attendee_id));
            }

            if (is_null($attendee)) {
                return $this->error404();
            }

            $res = $this->service->removeEventToAttendeeSchedule($summit, $attendee, intval($event_id));

            return $res ? $this->deleted() : $this->error400();

        } catch (Exception $ex) {
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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
            if ($attendee_id === 'me') {
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) {
                    return $this->error404();
                }
                $attendee = $summit->getAttendeeByMemberId($member_id);
            } else {
                $attendee = $summit->getAttendeeById(intval($attendee_id));
            }

            if (is_null($attendee)) {
                return $this->error404();
            }

            $res = $this->service->checkInAttendeeOnEvent($summit, $attendee, intval($event_id));

            return $res ? $this->updated() : $this->error400();
        } catch (Exception $ex) {
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

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit))
            {
                return $this->error404();
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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
            if ($speaker_id === 'me') {
                $member_id = $this->resource_server_context->getCurrentUserExternalId();
                if (is_null($member_id)) {
                    return $this->error404();
                }
                $speaker = $summit->getSpeakerByMemberId($member_id);
            } else {
                $speaker = $summit->getSpeakerById(intval($speaker_id));
            }

            if (is_null($speaker)) {
                return $this->error404();
            }

            $data = $speaker->toArray();

            if (!empty($expand)) {
                $expand = explode(',', $expand);
                foreach ($expand as $relation) {
                    switch (trim($relation)) {
                        case 'presentations': {
                            $presentations = array();
                            unset($data['presentations']);
                            foreach ($speaker->presentations() as $event) {
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

            $expand = Request::input('expand', '');

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }

            // default values
            $page     = 1;
            $per_page = 5;

            if (Input::has('page')) {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;
            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), array
                (
                    'title'      => array('=@', '=='),
                    'tags'       => array('=@', '=='),
                    'start_date' => array('>', '<', '<=', '>=', '=='),
                    'end_date'   => array('>', '<', '<=', '>=', '=='),
                ));
            }

            $schedule = array();
            list($total, $per_page, $current_page, $last_page, $items) = $summit->schedule($page, $per_page, $filter);

            foreach ($items as $event) {
                $data = $event->toArray();
                if (!empty($expand)) {
                    foreach (explode(',', $expand) as $relation) {
                        switch (trim($relation)) {
                            case 'feedback': {
                                $feedback = array();
                                list($total, $per_page, $current_page, $last_page, $items) = $event->feedback(1,
                                    PHP_INT_MAX);
                                foreach ($items as $f) {
                                    array_push($feedback, $f->toArray());
                                }
                                $data['feedback'] = $feedback;
                            }
                            break;
                        }
                    }
                }
                array_push($schedule, $data);
            }

            return $this->ok
            (
                array
                (
                    'total'        => $total,
                    'per_page'     => $per_page,
                    'current_page' => $current_page,
                    'last_page'    => $last_page,
                    'data'         => $schedule,
                )
            );
        } catch (Exception $ex) {
            Log::error($ex);

            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function getEvent($summit_id, $event_id)
    {
        try {
            $expand = Request::input('expand', '');

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $data = $event->toArray();

            if (!empty($expand)) {
                foreach (explode(',', $expand) as $relation) {
                    switch (trim($relation)) {
                        case 'feedback': {
                            $feedback = array();
                            list($total, $per_page, $current_page, $last_page, $items) = $event->feedback(1,
                                PHP_INT_MAX);
                            foreach ($items as $f) {
                                array_push($feedback, $f->toArray());
                            }
                            $data['feedback'] = $feedback;
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
     * @param $summit_id
     * @param $event_id
     * @return mixed
     */
    public function getEventFeedback($summit_id, $event_id)
    {

        try {

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

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            // default values
            $page     = 1;
            $per_page = 5;

            if (Input::has('page'))
            {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            list($total, $per_page, $current_page, $last_page, $feedback) = $event->feedback($page, $per_page);

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

            $data = Input::json();

            $rules = array
            (
                'rate'        => 'required|integer|digits_between:0,10',
                'note'        => 'required|max:500',
                'attendee_id' => 'required|integer'
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

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $data         = $data->all();
            $attendee_id  = intval($data['attendee_id']);
            $member_id    = $this->resource_server_context->getCurrentUserExternalId();

            if (is_null($member_id)) {
                return $this->error404();
            }

            $attendee  = $summit->getAttendeeByMemberId($member_id);
            if (is_null($attendee)) {
                return $this->error404();
            }
            if(intval($attendee->ID) !== $attendee_id){
                return $this->error401();
            }
            $res  = $this->service->addEventFeedback
            (
                $summit,
                $event,
                $data
            );

            return !is_null($res) ? $this->created($res->ID) : $this->error400();
        }
        catch (EntityNotFoundException $ex1) {
            Log::error($ex1);
            return $this->error404();
        }
        catch(ValidationException $ex2)
        {
            Log::error($ex2);
            return $this->error412(array($ex2->getMessage()));
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

            $last_event_id = Request::input('last_event_id', null);
            $from_date     = Request::input('from_date', null);

            $rules = array
            (
                'last_event_id' => 'sometimes|required|integer',
                'from_date'     => 'sometimes|required|integer',
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

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit))
            {
                return $this->error404();
            }

            if (!is_null($from_date))
            {
                $from_date = new \DateTime("@$from_date");
            }

            $list = $this->service->getSummitEntityEvents
            (
                $summit,
                $this->resource_server_context->getCurrentUserExternalId(),
                $from_date,
                $last_event_id
            );

            return $this->ok($list);
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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }

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
            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit)) {
                return $this->error404();
            }
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

}