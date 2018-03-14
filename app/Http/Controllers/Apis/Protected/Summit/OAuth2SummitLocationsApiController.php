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
use App\Http\Utils\PagingConstants;
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBannerConstants;
use App\Models\Foundation\Summit\Locations\SummitLocationConstants;
use App\Models\Foundation\Summit\Repositories\ISummitLocationBannerRepository;
use App\Models\Foundation\Summit\Repositories\ISummitLocationRepository;
use App\Services\Model\ILocationService;
use Exception;
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
use models\summit\Summit;
use models\summit\SummitAirport;
use models\summit\SummitExternalLocation;
use models\summit\SummitGeoLocatedLocation;
use models\summit\SummitHotel;
use models\summit\SummitVenue;
use models\summit\SummitVenueRoom;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\Filter;
use utils\FilterParser;
use utils\FilterParserException;
use utils\OrderParser;
use utils\PagingInfo;
use utils\PagingResponse;
use Illuminate\Http\Request as LaravelRequest;
use utils\ParseMultiPartFormDataInputStream;

/**
 * Class OAuth2SummitLocationsApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitLocationsApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitService
     */
    private $summit_service;

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
     * @var ISummitLocationRepository
     */
    private $location_repository;

    /**
     * @var ILocationService
     */
    private $location_service;

    /**
     * @var ISummitLocationBannerRepository
     */
    private $location_banners_repository;

    /**
     * OAuth2SummitLocationsApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $event_repository
     * @param ISpeakerRepository $speaker_repository
     * @param IEventFeedbackRepository $event_feedback_repository
     * @param ISummitLocationRepository $location_repository
     * @param ISummitLocationBannerRepository $location_banners_repository
     * @param ISummitService $summit_service
     * @param ILocationService $location_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $event_repository,
        ISpeakerRepository $speaker_repository,
        IEventFeedbackRepository $event_feedback_repository,
        ISummitLocationRepository $location_repository,
        ISummitLocationBannerRepository $location_banners_repository,
        ISummitService $summit_service,
        ILocationService $location_service,
        IResourceServerContext $resource_server_context
    ) {
        parent::__construct($resource_server_context);
        $this->repository                  = $summit_repository;
        $this->speaker_repository          = $speaker_repository;
        $this->event_repository            = $event_repository;
        $this->event_feedback_repository   = $event_feedback_repository;
        $this->location_repository         = $location_repository;
        $this->location_banners_repository = $location_banners_repository;
        $this->location_service            = $location_service;
        $this->summit_service              = $summit_service;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getLocations($summit_id)
    {
        $values = Input::all();
        $rules  = [

            'page'     => 'integer|min:1',
            'per_page' => sprintf('required_with:page|integer|min:%s|max:%s', PagingConstants::MinPageSize, PagingConstants::MaxPageSize),
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
            $per_page = PagingConstants::DefaultPageSize;

            if (Input::has('page')) {
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [
                    'class_name'  => ['=='],
                    'name'        => ['==', '=@'],
                    'description' => ['=@'],
                    'address_1'   => ['=@'],
                    'address_2'   => ['=@'],
                    'zip_code'    => ['==','=@'],
                    'city'        => ['==','=@'],
                    'state'       => ['==','=@'],
                    'country'     => ['==','=@'],
                    'sold_out'    => ['=='],
                    'is_main'     => ['=='],
                ]);
            }
            if(is_null($filter)) $filter = new Filter();

            $filter->validate([
                'class_name'  => sprintf('sometimes|in:%s',implode(',', SummitLocationConstants::$valid_class_names)),
                'name'        => 'sometimes|string',
                'description' => 'sometimes|string',
                'address_1'   => 'sometimes|string',
                'address_2'   => 'sometimes|string',
                'zip_code'    => 'sometimes|string',
                'city'        => 'sometimes|string',
                'state'       => 'sometimes|string',
                'country'     => 'sometimes|string',
                'sold_out'    => 'sometimes|boolean',
                'is_main'     => 'sometimes|boolean',
            ], [
                'class_name.in' =>  sprintf
                (
                    ":attribute has an invalid value ( valid values are %s )",
                    implode(", ", SummitLocationConstants::$valid_class_names)
                )
            ]);

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [
                    'id',
                    'name',
                    'order'
                ]);
            }

            $data = $this->location_repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

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
    public function getVenues($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();

            foreach ($summit->getVenues() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            $response    = new PagingResponse
            (
                count($locations),
                count($locations),
                1,
                1,
                $locations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getExternalLocations($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getExternalLocations() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            $response    = new PagingResponse
            (
                count($locations),
                count($locations),
                1,
                1,
                $locations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getHotels($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getHotels() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            $response    = new PagingResponse
            (
                count($locations),
                count($locations),
                1,
                1,
                $locations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getAirports($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getAirports() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            $response    = new PagingResponse
            (
                count($locations),
                count($locations),
                1,
                1,
                $locations
            );

            return $this->ok($response->toArray($expand = Input::get('expand','')));

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

            $expand    = Request::input('expand', '');
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $location = $summit->getLocation($location_id);
            if (is_null($location)) {
                return $this->error404();
            }

            if (!Summit::isPrimaryLocation($location)) {
                return $this->error404();
            }

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($location)->serialize($expand,[], $relations));
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param string $summit_id
     * @param string $location_id
     * @param bool $published
     * @return PagingResponse
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    private function _getLocationEvents($summit_id, $location_id, $published = true)
    {
        $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
        if (is_null($summit))
            throw new EntityNotFoundException;

        if(strtolower($location_id) != "tbd") {
            $location = $summit->getLocation(intval($location_id));
            if (is_null($location))
                throw new EntityNotFoundException;
        }

        $values = Input::all();

        $rules =
        [
            'page'     => 'integer|min:1',
            'per_page' => sprintf('required_with:page|integer|min:%s|max:%s', PagingConstants::MinPageSize, PagingConstants::MaxPageSize),
        ];

        $validation = Validator::make($values, $rules);

        if ($validation->fails()) {
            $ex = new ValidationException();
            throw $ex->setMessages($validation->messages()->toArray());
        }

        // default values
        $page     = 1;
        $per_page = PagingConstants::DefaultPageSize;

        if (Input::has('page')) {
            $page     = intval(Input::get('page'));
            $per_page = intval(Input::get('per_page'));
        }

        $filter = null;

        if (Input::has('filter')) {
            $filter = FilterParser::parse(Input::get('filter'),
            [
                'title'          => ['=@', '=='],
                'start_date'     => ['>', '<', '<=', '>=', '=='],
                'end_date'       => ['>', '<', '<=', '>=', '=='],
                'speaker'        => ['=@', '=='],
                'tags'           => ['=@', '=='],
                'event_type_id'  => ['=='],
                'track_id'       => ['==']
            ]);
        }

        $order = null;

        if (Input::has('order'))
        {
            $order = OrderParser::parse(Input::get('order'),
            [
                'title',
                'start_date',
                'end_date',
                'id',
                'created',
            ]);
        }

        if(is_null($filter)) $filter = new Filter();

        $filter->addFilterCondition(FilterParser::buildFilter('summit_id','==', $summit_id));

        if(intval($location_id) > 0)
            $filter->addFilterCondition(FilterParser::buildFilter('location_id','==', $location_id));

        if($published)
        {
            $filter->addFilterCondition(FilterParser::buildFilter('published','==', 1));
        }

        return strtolower($location_id) == "tbd" ?
            $this->event_repository->getAllByPageLocationTBD(new PagingInfo($page, $per_page), $filter, $order):
            $this->event_repository->getAllByPage(new PagingInfo($page, $per_page), $filter, $order);
    }

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function getLocationEvents($summit_id, $location_id)
    {
        try {
            return $this->ok($this->_getLocationEvents($summit_id, $location_id, false)->toArray(Request::input('expand', '')));
        }
        catch (EntityNotFoundException $ex1) {
            Log::warning($ex1);
            return $this->error404();
        }
        catch (ValidationException $ex2) {
            Log::warning($ex2);
            return $this->error412($ex2->getMessages());
        }
        catch(FilterParserException $ex3){
            Log::warning($ex3);
            return $this->error412($ex3->getMessages());
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
            return $this->ok($this->_getLocationEvents($summit_id, $location_id, true)->toArray(Request::input('expand', '')));
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
    public function getMetadata($summit_id){
        $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
        if (is_null($summit)) return $this->error404();

        return $this->ok
        (
            $this->location_repository->getMetadata($summit)
        );
    }

    /**
     * @param $summit_id
     * @param $venue_id
     * @param $floor_id
     * @return mixed
     */
    public function getVenueFloor($summit_id, $venue_id, $floor_id){
        try {

            $expand    = Request::input('expand', '');
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];

            $summit    = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $venue = $summit->getLocation($venue_id);

            if (is_null($venue)) {
                return $this->error404();
            }

            if (!$venue instanceof SummitVenue) {
                return $this->error404();
            }

            $floor = $venue->getFloor($floor_id);

            if (is_null($floor)) {
                return $this->error404();
            }

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($floor)->serialize($expand,[], $relations));
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
     * @param $venue_id
     * @param $room_id
     * @return mixed
     */
    public function getVenueRoom($summit_id, $venue_id, $room_id){
        try {

            $expand    = Request::input('expand', '');
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];

            $summit    = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $venue = $summit->getLocation($venue_id);

            if (is_null($venue)) {
                return $this->error404();
            }

            if (!$venue instanceof SummitVenue) {
                return $this->error404();
            }


            $room = $venue->getRoom($room_id);

            if (is_null($room)) {
                return $this->error404();
            }

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($room)->serialize($expand,[], $relations));
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
     * @param $venue_id
     * @param $floor_id
     * @param $room_id
     * @return mixed
     */
    public function getVenueFloorRoom($summit_id, $venue_id, $floor_id, $room_id){
        try {

            $expand    = Request::input('expand', '');
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];

            $summit    = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $venue = $summit->getLocation($venue_id);

            if (is_null($venue)) {
                return $this->error404();
            }

            if (!$venue instanceof SummitVenue) {
                return $this->error404();
            }

            $floor = $venue->getFloor($floor_id);

            if (is_null($floor)) {
                return $this->error404();
            }

            $room = $floor->getRoom($room_id);

            if (is_null($room)) {
                return $this->error404();
            }

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($room)->serialize($expand,[], $relations));
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

    /***
     * Add Locations Endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function addLocation($summit_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->addLocation($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @return mixed
     */
    public function addVenue($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitVenue::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->addLocation($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @return mixed
     */
    public function addExternalLocation($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitExternalLocation::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->addLocation($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @return mixed
     */
    public function addHotel($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitHotel::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->addLocation($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @return mixed
     */
    public function addAirport($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitAirport::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->addLocation($summit, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @param $venue_id
     * @return mixed
     */
    public function addVenueFloor($summit_id, $venue_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $rules = [
                'name'        => 'required|string|max:50',
                'number'      => 'required|integer',
                'description' => 'sometimes|string',
            ];
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $floor = $this->location_service->addVenueFloor($summit, $venue_id, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($floor)->serialize());
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
     * @param $venue_id
     * @return mixed
     */
    public function addVenueRoom($summit_id, $venue_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitVenueRoom::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $room = $this->location_service->addVenueRoom($summit, $venue_id, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($room)->serialize());
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
     * @param $venue_id
     * @return mixed
     */
    public function addVenueFloorRoom($summit_id, $venue_id, $floor_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitVenueRoom::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $payload['floor_id'] = intval($floor_id);

            $room = $this->location_service->addVenueRoom($summit, $venue_id, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($room)->serialize());
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
     *  Update Location Endpoints
     */

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function updateLocation($summit_id, $location_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->updateLocation($summit, $location_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @param $venue_id
     * @return mixed
     */
    public function updateVenue($summit_id, $venue_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitVenue::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->updateLocation($summit, $venue_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @param $venue_id
     * @param $floor_id
     * @return mixed
     */
    public function updateVenueFloor($summit_id, $venue_id, $floor_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $rules = [
                'name'        => 'sometimes|string|max:50',
                'number'      => 'sometimes|integer',
                'description' => 'sometimes|string',
            ];
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $floor = $this->location_service->updateVenueFloor($summit, $venue_id, $floor_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($floor)->serialize());
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
     * @param $venue_id
     * @param $room_id
     * @return mixed
     */
    public function updateVenueRoom($summit_id, $venue_id, $room_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitVenueRoom::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $room = $this->location_service->updateVenueRoom($summit, $venue_id, $room_id, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($room)->serialize());
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
     * @param $venue_id
     * @param $floor_id
     * @param $room_id
     * @return mixed
     */
    public function updateVenueFloorRoom($summit_id, $venue_id, $floor_id, $room_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitVenueRoom::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            if(!isset($payload['floor_id']))
                $payload['floor_id'] = intval($floor_id);

            $room = $this->location_service->updateVenueRoom($summit, $venue_id, $room_id, $payload);

            return $this->created(SerializerRegistry::getInstance()->getSerializer($room)->serialize());
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
     * @param $hotel_id
     * @return mixed
     */
    public function updateHotel($summit_id, $hotel_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitHotel::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->updateLocation($summit, $hotel_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @param $airport_id
     * @return mixed
     */
    public function updateAirport($summit_id, $airport_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitAirport::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->updateLocation($summit, $airport_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * @param $external_location_id
     * @return mixed
     */
    public function updateExternalLocation($summit_id, $external_location_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $payload['class_name'] = SummitExternalLocation::ClassName;
            $rules = SummitLocationValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($payload, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $location = $this->location_service->updateLocation($summit, $external_location_id, $payload);

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
     * Delete Location Endpoints
     */

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function deleteLocation($summit_id, $location_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->location_service->deleteLocation($summit, $location_id);

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

    public function deleteVenueFloor($summit_id, $venue_id, $floor_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->location_service->deleteVenueFloor($summit, $venue_id, $floor_id);

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

    public function deleteVenueRoom($summit_id, $venue_id, $room_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->location_service->deleteVenueRoom($summit, $venue_id, $room_id);

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

    /**
     *  Location Banners Endpoints
     */

    /**
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function getLocationBanners($summit_id, $location_id){
        $values = Input::all();
        $rules  = [
            'page'     => 'integer|min:1',
            'per_page' => sprintf('required_with:page|integer|min:%s|max:%s', PagingConstants::MinPageSize, PagingConstants::MaxPageSize),
        ];

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $location = $summit->getLocation($location_id);
            if (is_null($location)) return $this->error404();

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }

            // default values
            $page     = 1;
            $per_page = PagingConstants::DefaultPageSize;

            if (Input::has('page')) {
                $page     = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [
                    'class_name'  => ['=='],
                    'title'       => ['==', '=@'],
                    'content'     => ['=@'],
                    'type'        => ['=='],
                    'enabled'     => ['=='],
                    'start_date'  => ['>', '<', '<=', '>=', '=='],
                    'end_date'    => ['>', '<', '<=', '>=', '=='],
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            $filter->validate([
                'class_name' => sprintf('sometimes|in:%s',implode(',', SummitLocationBannerConstants::$valid_class_names)),
                'title'      => 'sometimes|string',
                'content'    => 'sometimes|string',
                'type'       => sprintf('sometimes|in:%s',implode(',', SummitLocationBannerConstants::$valid_types)),
                'enabled'    => 'sometimes|boolean',
                'start_date' => 'sometimes|date_format:U',
                'end_date'   => 'sometimes|date_format:U',
            ], [
                'class_name.in' =>  sprintf
                (
                    ":attribute has an invalid value ( valid values are %s )",
                    implode(", ", SummitLocationBannerConstants::$valid_class_names)
                )
            ]);

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [
                    'id',
                    'title',
                    'start_date',
                    'end_date'
                ]);
            }

            $data = $this->location_banners_repository->getBySummitLocation($location, new PagingInfo($page, $per_page), $filter, $order);

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
     * @param $location_id
     * @return mixed
     */
    public function addLocationBanner($summit_id, $location_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitLocationBannerValidationRulesFactory::build($payload);
            // Creates a Validator instance and validates the data.
            $messages =  [
                'class_name.in' =>  sprintf
                (
                    ":attribute has an invalid value ( valid values are %s )",
                    implode(", ", SummitLocationBannerConstants::$valid_class_names)
                )
            ];
            $validation = Validator::make($payload, $rules, $messages);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $banner = $this->location_service->addLocationBanner
            (
                $summit,
                $location_id,
                HTMLCleaner::cleanData
                (
                    $payload, ['title', 'content']
                )
            );

            return $this->created(SerializerRegistry::getInstance()->getSerializer($banner)->serialize());
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
     * @param $location_id
     * @param $banner_id
     * @return mixed
     */
    public function deleteLocationBanner($summit_id, $location_id, $banner_id){
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->location_service->deleteLocationBanner($summit, $location_id, $banner_id);

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
     * @param $location_id
     * @param $banner_id
     * @return mixed
     */
    public function updateLocationBanner($summit_id, $location_id, $banner_id){
        try {

            if(!Request::isJson()) return $this->error400();
            $payload = Input::json()->all();

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = SummitLocationBannerValidationRulesFactory::build($payload, true);
            // Creates a Validator instance and validates the data.
            $messages =  [
                'class_name.in' =>  sprintf
                (
                    ":attribute has an invalid value ( valid values are %s )",
                    implode(", ", SummitLocationBannerConstants::$valid_class_names)
                )
            ];
            $validation = Validator::make($payload, $rules, $messages);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $banner = $this->location_service->updateLocationBanner
            (
                $summit,
                $location_id,
                $banner_id,
                HTMLCleaner::cleanData
                (
                    $payload, ['title', 'content']
                )
            );

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($banner)->serialize());
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
     *  Location Maps endpoints
     */


    /**
     * @param $summit_id
     * @param $location_id
     * @param $map_id
     * @return mixed
     */
    public function getLocationMap($summit_id, $location_id, $map_id){
        try {

            $expand    = Request::input('expand', '');
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $location = $summit->getLocation($location_id);
            if (is_null($location)) {
                return $this->error404();
            }

            if (!Summit::isPrimaryLocation($location)) {
                return $this->error404();
            }

            $map = $location->getMap($map_id);
            if (is_null($map)) {
                return $this->error404();
            }

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($map)->serialize($expand,[], $relations));

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function addLocationMap(LaravelRequest $request, $summit_id, $location_id){

        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $file      = $request->file('file');

            if(is_null($file))
                throw new ValidationException('file is required.');

            $metadata  = $request->all();

            $rules     = SummitLocationImageValidationRulesFactory::build();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($metadata, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $map = $this->location_service->addLocationMap
            (
                $summit,
                $location_id,
                HTMLCleaner::cleanData
                (
                    $metadata, ['description']
                ),
                $file
            );

            return $this->created(SerializerRegistry::getInstance()->getSerializer($map)->serialize());
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
     * @param $location_id
     * @param $map_id
     * @return mixed
     */
    public function updateLocationMap(LaravelRequest $request, $summit_id, $location_id, $map_id){

        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $content_type = $request->headers->has('Content-Type')  ? strtolower( $request->headers->get('Content-Type')) : null;

            if (false !== $pos = strpos($content_type, ';')) {
                $content_type = substr($content_type, 0, $pos);
            }

            if(!strstr($content_type, 'multipart/form-data'))
                return $this->error400();

            $parser   = new ParseMultiPartFormDataInputStream(file_get_contents('php://input'));
            $input    = $parser->getInput();
            $metadata = $input['parameters'];
            $files    = $input['files'];
            $file     = null;

            if(isset($files['file']))
                $file = $files['file'];

            $rules      = SummitLocationImageValidationRulesFactory::build(true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($metadata, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $map = $this->location_service->updateLocationMap
            (
                $summit,
                $location_id,
                $map_id,
                HTMLCleaner::cleanData
                (
                    $metadata, ['description']
                ),
                $file
            );

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($map)->serialize());
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
     * @param $location_id
     * @param $map_id
     * @return mixed
     */
    public function deleteLocationMap($summit_id, $location_id, $map_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $this->location_service->deleteLocationMap($summit, $location_id, $map_id);
            return $this->deleted();
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
     *  Location Images endpoints
     */

    /**
     * @param $summit_id
     * @param $location_id
     * @param $image_id
     * @return mixed
     */
    public function getLocationImage($summit_id, $location_id, $image_id){
        try {

            $expand    = Request::input('expand', '');
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $location = $summit->getLocation($location_id);
            if (is_null($location)) {
                return $this->error404();
            }

            if(!$location instanceof SummitGeoLocatedLocation){
                return $this->error404();
            }

            $image = $location->getImage($image_id);
            if (is_null($image)) {
                return $this->error404();
            }

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($image)->serialize($expand,[], $relations));

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
    /**
     * @param LaravelRequest $request
     * @param $summit_id
     * @param $location_id
     * @return mixed
     */
    public function addLocationImage(LaravelRequest $request, $summit_id, $location_id){

        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $file      = $request->file('file');
            if(is_null($file))
                throw new ValidationException('file is required.');

            $metadata  = $request->all();

            $rules     = SummitLocationImageValidationRulesFactory::build();
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($metadata, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $image = $this->location_service->addLocationImage
            (
                $summit,
                $location_id,
                HTMLCleaner::cleanData
                (
                    $metadata, ['description']
                ),
                $file
            );

            return $this->created(SerializerRegistry::getInstance()->getSerializer($image)->serialize());
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
     * @param $location_id
     * @param $image_id
     * @return mixed
     */
    public function updateLocationImage(LaravelRequest $request, $summit_id, $location_id, $image_id){

        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $content_type = $request->headers->has('Content-Type')  ? strtolower( $request->headers->get('Content-Type')) : null;

            if (false !== $pos = strpos($content_type, ';')) {
                $content_type = substr($content_type, 0, $pos);
            }

            if(!strstr($content_type, 'multipart/form-data'))
                return $this->error400();

            $parser   = new ParseMultiPartFormDataInputStream(file_get_contents('php://input'));
            $input    = $parser->getInput();
            $metadata = $input['parameters'];
            $files    = $input['files'];
            $file     = null;

            if(isset($files['file']))
                $file = $files['file'];

            $rules      = SummitLocationImageValidationRulesFactory::build(true);
            // Creates a Validator instance and validates the data.
            $validation = Validator::make($metadata, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $image = $this->location_service->updateLocationImage
            (
                $summit,
                $location_id,
                $image_id,
                HTMLCleaner::cleanData
                (
                    $metadata, ['description']
                ),
                $file
            );

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($image)->serialize());
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
     * @param $location_id
     * @param $image_id
     * @return mixed
     */
    public function deleteLocationImage($summit_id, $location_id, $image_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $this->location_service->deleteLocationImage($summit, $location_id, $image_id);
            return $this->deleted();
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

}