<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use services\model\ISummitService;
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
     * @param ISummitRepository $repository
     * @param ISummitService $service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $repository,
        ISummitService $service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->repository = $repository;
        $this->service = $service;
    }

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
                            foreach ($summit->attendees() as $attendee) {
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
                            foreach ($summit->speakers() as $speaker) {
                                array_push($speakers, $speaker->toArray());
                            }
                            $data['speakers'] = $speakers;

                            $presentation_categories = array();
                            foreach ($summit->presentation_categories() as $cat) {
                                array_push($presentation_categories, $cat->toArray());
                            }
                            $data['tracks'] = $presentation_categories;

                            $schedule = array();
                            foreach ($summit->schedule() as $event) {
                                array_push($schedule, $event->toArray());
                            }
                            $data['schedule'] = $schedule;

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
     *  Attendees Endpoints
     */

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

            if (!empty($expand)) {
                $expand = explode(',', $expand);
                foreach ($expand as $relation) {
                    switch (trim($relation)) {
                        case 'schedule': {
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
     *  Speakers Endpoints
     */

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
                            foreach ($speaker->presentations() as $event) {
                                $event->setFromSpeaker();
                                array_push($presentations, $event->toArray());
                            }
                            $data['presentations'] = $presentations;
                        }
                        break;
                        case 'feedback': {
                            $feedback = array();
                            foreach ($speaker->feedback() as $f) {
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


    public function addSpeakerFeedback(Request $request, $summit_id, $speaker_id, $presentation_id)
    {
        if (!$request->isJson()) {
            return $this->error412(array('invalid content type!'));
        }
        $data = Input::json();

        $rules = array
        (
            'rate' => 'required|integer|digits_between:0,10',
            'note' => 'required',
            'owner_id' => 'required|integer'
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

        try {

            $summit = $summit_id === 'current' ?
                $this->repository->getCurrent() :
                $this->repository->getById(intval($summit_id));

            if (is_null($summit))
            {
                return $this->error404();
            }

            $speaker = $summit->getSpeakerById(intval($speaker_id));

            if (is_null($speaker))
            {
                return $this->error404();
            }

            $presentation = $speaker->getPresentation($presentation_id);

            if (is_null($presentation))
            {
                return $this->error404();
            }

            $res = $this->service->addSpeakerFeedback
            (
                $summit,
                $speaker,
                $presentation,
                $data->all()
            );

            return $res ? $this->created() : $this->error400();

        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}