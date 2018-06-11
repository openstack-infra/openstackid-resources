<?php namespace App\Http\Controllers;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Services\Model\ISummitSelectionPlanService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use Exception;
use ModelSerializers\SerializerRegistry;
/**
 * Class OAuth2SummitSelectionPlansApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitSelectionPlansApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var ISummitSelectionPlanService
     */
    private $selection_plan_service;

    /**
     * OAuth2SummitSelectionPlansApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitSelectionPlanService $selection_plan_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitSelectionPlanService $selection_plan_service,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);

        $this->summit_repository      = $summit_repository;
        $this->selection_plan_service = $selection_plan_service;
    }

    /**
     * @param $summit_id
     * @param $selection_plan_id
     * @return mixed
     */
    public function getSelectionPlan($summit_id, $selection_plan_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $selection_plan = $summit->getSelectionPlanById($selection_plan_id);
            if (is_null($selection_plan)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($selection_plan)->serialize(Request::input('expand', '')));
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $selection_plan_id
     * @return mixed
     */
    public function updateSelectionPlan($summit_id, $selection_plan_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitSelectionPlanValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $selection_plan = $this->selection_plan_service->updateSelectionPlan($summit, $selection_plan_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($selection_plan)->serialize());
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
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
    public function addSelectionPlan($summit_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitSelectionPlanValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $selection_plan = $this->selection_plan_service->addSelectionPlan($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($selection_plan)->serialize());
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $selection_plan_id
     * @return mixed
     */
    public function deleteSelectionPlan($summit_id, $selection_plan_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->selection_plan_service->deleteSelectionPlan($summit, $selection_plan_id);

            return $this->deleted();
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $selection_plan_id
     * @param $track_group_id
     * @return mixed
     */
    public function addTrackGroupToSelectionPlan($summit_id, $selection_plan_id, $track_group_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->selection_plan_service->addTrackGroupToSelectionPlan($summit, $selection_plan_id, $track_group_id);

            return $this->deleted();
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $selection_plan_id
     * @param $track_group_id
     * @return mixed
     */
    public function deleteTrackGroupToSelectionPlan($summit_id, $selection_plan_id, $track_group_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->selection_plan_service->deleteTrackGroupToSelectionPlan($summit, $selection_plan_id, $track_group_id);

            return $this->deleted();
        }
        catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412([$ex1->getMessage()]);
        }
        catch(EntityNotFoundException $ex2)
        {
            Log::warning($ex2);
            return $this->error404(['message'=> $ex2->getMessage()]);
        }
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}