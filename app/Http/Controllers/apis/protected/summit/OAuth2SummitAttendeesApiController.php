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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;

/**
 * Class OAuth2SummitAttendeesApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitAttendeesApiController extends OAuth2ProtectedController
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
     *  Attendees endpoints
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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $type     = $attendee_id === 'me' ? CheckAttendeeStrategyFactory::Me : CheckAttendeeStrategyFactory::Own;
            $attendee = CheckAttendeeStrategyFactory::build($type, $this->resource_server_context)->check($attendee_id, $summit);
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
    public function getAttendeeSchedule($summit_id, $attendee_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee =  CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if(is_null($attendee)) return $this->error404();

            $schedule = array();
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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $this->service->addEventToMemberSchedule($summit, $attendee->getMember(), intval($event_id));

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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $this->service->removeEventFromMemberSchedule($summit, $attendee->getMember(), intval($event_id));

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

            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $attendee = CheckAttendeeStrategyFactory::build(CheckAttendeeStrategyFactory::Own, $this->resource_server_context)->check($attendee_id, $summit);
            if (is_null($attendee)) return $this->error404();

            $this->service->unRSVPEvent($summit, $attendee->getMember(), $event_id);

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

}