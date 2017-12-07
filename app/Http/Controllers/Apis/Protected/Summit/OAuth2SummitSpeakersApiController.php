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
use models\oauth2\IResourceServerContext;
use models\summit\IEventFeedbackRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISummitService;
use utils\FilterParser;
use utils\FilterParserException;
use utils\OrderParser;
use utils\PagingInfo;

/**
 * Class OAuth2SummitSpeakersApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitSpeakersApiController extends OAuth2ProtectedController
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
     *  Speakers endpoints
     */

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getSpeakers($summit_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
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
                $result->toArray(Request::input('expand', ''),[],[],['summit_id' => $summit_id, 'published' => true])
            );
        }
        catch(FilterParserException $ex1){
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        }
        catch (Exception $ex)
        {
            Log::error($ex);
            return $this->error500($ex);
        }
    }


    public function getAll(){
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

            $result = $this->speaker_repository->getAllByPage(new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray(Request::input('expand', ''),[],[])
            );
        }
        catch(FilterParserException $ex1){
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
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
        try
        {

            $summit = SummitFinderStrategyFactory::build($this->repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $speaker = CheckSpeakerStrategyFactory::build(CheckSpeakerStrategyFactory::Me, $this->resource_server_context)->check($speaker_id, $summit);
            if (is_null($speaker)) return $this->error404();

            return $this->ok(SerializerRegistry::getInstance()->getSerializer($speaker)->serialize(Request::input('expand', '')));

        } catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }


}