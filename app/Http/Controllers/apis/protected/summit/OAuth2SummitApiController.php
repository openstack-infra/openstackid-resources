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
use Illuminate\Support\Facades\Validator;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ConfirmationExternalOrderRequest;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\PagingResponse;

/**
 * Class OAuth2SummitApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitApiController extends OAuth2ProtectedController
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

    public function getSummits()
    {
        try {
            $summits = array();

            foreach($this->repository->getAll() as $summit){
                $summits[] = SerializerRegistry::getInstance()->getSerializer($summit)->serialize();
            }

            $response = new PagingResponse
            (
                count($summits),
                count($summits),
                1,
                1,
                $summits
            );
            return $this->ok($response->toArray());
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
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($summit)->serialize($expand));
        } catch (Exception $ex) {
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
            $event_types = array();
            foreach ($summit->getEventTypes() as $event_type)
            {
                $event_types[] = SerializerRegistry::getInstance()->getSerializer($event_type)->serialize();
            }

            $response = new PagingResponse
            (
                count($event_types),
                count($event_types),
                1,
                1,
                $event_types
            );

            return $this->ok($response->toArray());
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getTracks($summit_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //tracks
            $tracks = array();
            foreach ($summit->getPresentationCategories() as $track)
            {
                $tracks[] = SerializerRegistry::getInstance()->getSerializer($track)->serialize(Request::input('expand', ''));
            }

            $response = new PagingResponse
            (
                count($tracks),
                count($tracks),
                1,
                1,
                $tracks
            );

            return $this->ok($response->toArray());
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $track_id
     * @return mixed
     */
    public function getTrack($summit_id, $track_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $track = $summit->getPresentationCategory($track_id);
            if (is_null($track)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($track)->serialize(Request::input('expand', '')));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getTracksGroups($summit_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //track groups
            $groups = array();
            foreach ($summit->getCategoryGroups() as $group)
            {
                $groups[] = SerializerRegistry::getInstance()->getSerializer($group)->serialize(Request::input('expand', ''));
            }

            $response = new PagingResponse
            (
                count($groups),
                count($groups),
                1,
                1,
                $groups
            );

            return $this->ok($response->toArray());
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $track_group_id
     * @return mixed
     */
    public function getTrackGroup($summit_id, $track_group_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $group = $summit->getCategoryGroup($track_group_id);
            if (is_null($group)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($group)->serialize(Request::input('expand', '')));
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

            $attendee = $this->service->confirmExternalOrderAttendee
            (
                new ConfirmationExternalOrderRequest
                (
                    $summit,
                    intval($member_id),
                    trim($external_order_id),
                    trim($external_attendee_id)
                )
            );

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($attendee)->serialize());
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