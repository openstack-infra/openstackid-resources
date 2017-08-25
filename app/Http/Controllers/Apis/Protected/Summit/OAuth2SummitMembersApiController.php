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

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\PagingResponse;
use Illuminate\Support\Facades\Input;

/**
 * Class OAuth2SummitMembersApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitMembersApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var ISummitService
     */
    private $summit_service;

    /**
     * OAuth2SummitMembersApiController constructor.
     * @param IMemberRepository $member_repository
     * @param ISummitRepository $summit_repository
     * @param ISummitService $summit_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        IMemberRepository $member_repository,
        ISummitRepository $summit_repository,
        ISummitService    $summit_service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->summit_repository  = $summit_repository;
        $this->repository         = $member_repository;
        $this->summit_service     = $summit_service;
    }

    public function getMyMember($summit_id, $member_id){

        $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
        if (is_null($summit)) return $this->error404();

        $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
        if (is_null($current_member_id)) return $this->error403();

        $current_member = $this->repository->getById($current_member_id);
        if (is_null($current_member)) return $this->error404();

        $fields    = Request::input('fields', null);
        $relations = Request::input('relations', null);

        return $this->ok
        (
            SerializerRegistry::getInstance()->getSerializer($current_member, SerializerRegistry::SerializerType_Private)
            ->serialize
            (
                Request::input('expand', ''),
                is_null($fields) ? [] : explode(',', $fields),
                is_null($relations) ? [] : explode(',', $relations),
                ['summit' => $summit]
            )
        );
    }

    public function getMemberFavoritesSummitEvents($summit_id, $member_id){

        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $favorites = array();
            foreach ($current_member->getFavoritesSummitEventsBySummit($summit) as $favorite_event)
            {
                if(!$summit->isEventOnSchedule($favorite_event->getEvent()->getId())) continue;
                $favorites[] = SerializerRegistry::getInstance()->getSerializer($favorite_event)->serialize(Request::input('expand', ''));
            }

            $response    = new PagingResponse
            (
                count($favorites),
                count($favorites),
                1,
                1,
                $favorites
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));
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
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $member_id
     * @param $event_id
     * @return mixed
     */
    public function addEventToMemberFavorites($summit_id, $member_id, $event_id){

        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $this->summit_service->addEventToMemberFavorites($summit, $current_member, intval($event_id));

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
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $member_id
     * @param $event_id
     * @return mixed
     */
    public function removeEventFromMemberFavorites($summit_id, $member_id, $event_id){

        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $this->summit_service->removeEventFromMemberFavorites($summit, $current_member, intval($event_id));

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
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $member_id
     * @return mixed
     */
    public function getMemberScheduleSummitEvents($summit_id, $member_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $schedule = array();
            foreach ($current_member->getScheduleBySummit($summit) as $schedule_event)
            {
                if(!$summit->isEventOnSchedule($schedule_event->getEvent()->getId())) continue;
                $schedule[] = SerializerRegistry::getInstance()->getSerializer($schedule_event)->serialize(Request::input('expand', ''));
            }

            $response    = new PagingResponse
            (
                count($schedule),
                count($schedule),
                1,
                1,
                $schedule
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));
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
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $member_id
     * @param $event_id
     * @return mixed
     */
    public function addEventToMemberSchedule($summit_id, $member_id, $event_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $this->summit_service->addEventToMemberSchedule($summit, $current_member, intval($event_id));

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
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $member_id
     * @param $event_id
     * @return mixed
     */
    public function removeEventFromMemberSchedule($summit_id, $member_id, $event_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $this->summit_service->removeEventFromMemberSchedule($summit, $current_member, intval($event_id));

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
        catch (\Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $member_id
     * @param $event_id
     * @return mixed
     */
    public function deleteEventRSVP($summit_id, $member_id, $event_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $current_member_id = $this->resource_server_context->getCurrentUserExternalId();
            if (is_null($current_member_id)) return $this->error403();

            $current_member = $this->repository->getById($current_member_id);
            if (is_null($current_member)) return $this->error404();

            $event = $summit->getScheduleEvent(intval($event_id));

            if (is_null($event)) {
                return $this->error404();
            }

            $this->summit_service->unRSVPEvent($summit, $current_member, $event_id);

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