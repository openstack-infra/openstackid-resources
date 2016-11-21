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
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\Filter;
use utils\FilterParser;
use utils\FilterParserException;
use utils\OrderParser;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class OAuth2SummitLocationsApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitLocationsApiController extends OAuth2ProtectedController
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

    public function getLocations($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getLocations() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
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
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($location)->serialize());
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
                'start_date'     => array('>', '<', '<=', '>=', '=='),
                'end_date'       => array('>', '<', '<=', '>=', '=='),
                'speaker'        => array('=@', '=='),
                'tags'           => array('=@', '=='),
                'event_type_id'  => array('=='),
                'track_id'       => array('=='),
            ));
        }

        $order = null;

        if (Input::has('order'))
        {
            $order = OrderParser::parse(Input::get('order'), array
            (
                'title',
                'start_date',
                'end_date',
                'id',
                'created',
            ));
        }

        if(is_null($filter)) $filter = new Filter();

        $filter->addFilterCondition(FilterParser::buildFilter('location_id','==', $location_id));

        if($published)
        {
            $filter->addFilterCondition(FilterParser::buildFilter('published','==', 1));
        }

        return $this->event_repository->getAllByPage(new PagingInfo($page, $per_page), $filter, $order);
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
     * @return mixed
     */
    public function getVenues($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();

            foreach ($summit->getVenues() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            return $this->ok($locations);

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
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getExternalLocations() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            return $this->ok($locations);
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
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getHotels() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            return $this->ok($locations);
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
            $summit = SummitFinderStrategyFactory::build($this->repository)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            //locations
            $locations = array();
            foreach ($summit->getAirports() as $location)
            {
                $locations[] = SerializerRegistry::getInstance()->getSerializer($location)->serialize();
            }

            return $this->ok($locations);
        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

}