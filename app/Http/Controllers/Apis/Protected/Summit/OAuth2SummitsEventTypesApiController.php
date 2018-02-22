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
use App\Http\Utils\BooleanCellFormatter;
use App\Http\Utils\EpochCellFormatter;
use App\Services\Model\ISummitEventTypeService;
use Illuminate\Support\Facades\Request;
use App\Models\Foundation\Summit\Events\SummitEventTypeConstants;
use App\Models\Foundation\Summit\Repositories\ISummitEventTypeRepository;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use models\exceptions\EntityNotFoundException;
use ModelSerializers\SerializerRegistry;
use utils\Filter;
use utils\FilterElement;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;
use Exception;
use utils\PagingResponse;
/**
 * Class OAuth2SummitsEventTypesApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitsEventTypesApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var ISummitEventTypeService
     */
    private $event_type_service;

    /**
     * OAuth2SummitsEventTypesApiController constructor.
     * @param ISummitEventTypeRepository $repository
     * @param ISummitRepository $summit_repository
     * @param ISummitEventTypeService $event_type_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitEventTypeRepository $repository,
        ISummitRepository $summit_repository,
        ISummitEventTypeService $event_type_service,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->repository         = $repository;
        $this->summit_repository  = $summit_repository;
        $this->event_type_service = $event_type_service;
    }

    /**
     * @param $filter_element
     * @return bool
     */
    private function validateClassName($filter_element){
        if($filter_element instanceof FilterElement){
            return in_array($filter_element->getValue(), SummitEventTypeConstants::$valid_class_names);
        }
        $valid = true;
        foreach($filter_element[0] as $elem){
            $valid = $valid && in_array($elem->getValue(), SummitEventTypeConstants::$valid_class_names);
        }
        return $valid;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAllBySummit($summit_id){
        $values = Input::all();
        $rules  = [

            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        ];

        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
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
                    'name'                       => ['=@', '=='],
                    'class_name'                 => ['=='],
                    'is_default'                 => ['=='],
                    'black_out_times'            => ['=='],
                    'use_sponsors'               => ['=='],
                    'are_sponsors_mandatory'     => ['=='],
                    'allows_attachment'          => ['=='],
                    'use_speakers'               => ['=='],
                    'are_speakers_mandatory'     => ['=='],
                    'use_moderator'              => ['=='],
                    'is_moderator_mandatory'     => ['=='],
                    'should_be_available_on_cfp' => ['=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'name',
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            if($filter->hasFilter("class_name") && !$this->validateClassName($filter->getFilter("class_name"))){
                throw new ValidationException(
                    sprintf
                    (
                        "class_name filter has an invalid value ( valid values are %s",
                        implode(", ", SummitEventTypeConstants::$valid_class_names)
                    )
                );
            }

            $data = $this->repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $data->toArray
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    []
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
     * @param $summit_id
     * @return mixed
     */
    public function getAllBySummitCSV($summit_id){
        $values = Input::all();
        $rules  = [
        ];

        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }

            // default values
            $page     = 1;
            $per_page = PHP_INT_MAX;

            if (Input::has('page')) {
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [
                    'name'                       => ['=@', '=='],
                    'class_name'                 => ['=='],
                    'is_default'                 => ['=='],
                    'black_out_times'            => ['=='],
                    'use_sponsors'               => ['=='],
                    'are_sponsors_mandatory'     => ['=='],
                    'allows_attachment'          => ['=='],
                    'use_speakers'               => ['=='],
                    'are_speakers_mandatory'     => ['=='],
                    'use_moderator'              => ['=='],
                    'is_moderator_mandatory'     => ['=='],
                    'should_be_available_on_cfp' => ['=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'name',
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            if($filter->hasFilter("class_name") && !$this->validateClassName($filter->getFilter("class_name"))){
                throw new ValidationException(
                    sprintf
                    (
                        "class_name filter has an invalid value ( valid values are %s",
                        implode(", ", SummitEventTypeConstants::$valid_class_names)
                    )
                );
            }

            $data = $this->repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            $filename = "event-types-" . date('Ymd');
            $list     =  $data->toArray();
            return $this->export
            (
                'csv',
                $filename,
                $list['data'],
                [
                    'created'                    => new EpochCellFormatter,
                    'last_edited'                => new EpochCellFormatter,
                    'is_default'                 => new BooleanCellFormatter,
                    'black_out_times'            => new BooleanCellFormatter,
                    'use_sponsors'               => new BooleanCellFormatter,
                    'are_sponsors_mandatory'     => new BooleanCellFormatter,
                    'allows_attachment'          => new BooleanCellFormatter,
                    'use_speakers'               => new BooleanCellFormatter,
                    'are_speakers_mandatory'     => new BooleanCellFormatter,
                    'use_moderator'              => new BooleanCellFormatter,
                    'is_moderator_mandatory'     => new BooleanCellFormatter,
                    'should_be_available_on_cfp' => new BooleanCellFormatter,
                ]
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
     * @param $summit_id
     * @param $event_type_id
     * @return mixed
     */
    public function getEventTypeBySummit($summit_id, $event_type_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $event_type = $summit->getEventType($event_type_id);
            if(is_null($event_type))
                return $this->error404();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($event_type)->serialize( Request::input('expand', '')));
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
     * @return mixed
     */
    public function addEventTypeBySummit($summit_id){
        try {

            if(!Request::isJson()) return $this->error403();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = EventTypeValidationRulesFactory::build($data->all());
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $event_type = $this->event_type_service->addEventType($summit, $data->all());

            return $this->created(SerializerRegistry::getInstance()->getSerializer($event_type)->serialize());
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
     * @param $event_type_id
     * @return mixed
     */
    public function updateEventTypeBySummit($summit_id, $event_type_id)
    {
        try {
            if (!Request::isJson()) return $this->error403();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = EventTypeValidationRulesFactory::build($data->all(), true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $event_type = $this->event_type_service->updateEventType($summit, $event_type_id, $data->all());

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($event_type)->serialize());
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
     * @param $event_type_id
     * @return mixed
     */
    public function deleteEventTypeBySummit($summit_id, $event_type_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->event_type_service->deleteEventType($summit, $event_type_id);

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
     * @param $summit_id
     * @return mixed
     */
    public function seedDefaultEventTypesBySummit($summit_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $event_types = $this->event_type_service->seedDefaultEventTypes($summit);

            $response = new PagingResponse
            (
                count($event_types),
                count($event_types),
                1,
                1,
                $event_types
            );

            return $this->created($response->toArray());
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