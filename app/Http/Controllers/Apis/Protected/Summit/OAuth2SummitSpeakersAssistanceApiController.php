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
use App\Models\Foundation\Summit\Repositories\IPresentationSpeakerSummitAssistanceConfirmationRequestRepository;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use ModelSerializers\SerializerRegistry;
use services\model\ISpeakerService;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;

/**
 * Class OAuth2SummitSpeakersAssistanceApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitSpeakersAssistanceApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var IPresentationSpeakerSummitAssistanceConfirmationRequestRepository
     */
    private $speakers_assistance_repository;

    /**
     * @var ISpeakerService
     */
    private $service;


    public function __construct
    (
        ISummitRepository $summit_repository,
        IPresentationSpeakerSummitAssistanceConfirmationRequestRepository $speakers_assistance_repository,
        ISpeakerService $service,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->summit_repository = $summit_repository;
        $this->service = $service;
        $this->speakers_assistance_repository = $speakers_assistance_repository;
    }

    /**
     * @param $summit_id
     * @return mixed
     */
    public function getBySummit($summit_id)
    {
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $values = Input::all();

            $rules = array
            (
                'page' => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:10|max:100',
            );

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412($messages);
            }

            // default values
            $page = 1;
            $per_page = 10;

            if (Input::has('page')) {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;

            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), [
                    'id' => ['=='],
                    'on_site_phone' => ['==', '=@'],
                    'speaker_email' => ['==', '=@'],
                    'speaker' => ['==', '=@'],
                    'is_confirmed' => ['=='],
                    'registered' => ['=='],
                    'confirmation_date' => ['>', '<', '>=', '<=']
                ]);
            }

            $order = null;
            if (Input::has('order')) {
                $order = OrderParser::parse(Input::get('order'), [
                    'id',
                    'is_confirmed',
                    'confirmation_date',
                    'created',
                    'registered',
                ]);
            }

            $serializer_type = SerializerRegistry::SerializerType_Private;
            $result = $this->speakers_assistance_repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            return $this->ok
            (
                $result->toArray
                (
                    Request::input('expand', ''),
                    [],
                    [],
                    [
                        'summit' => $summit,
                        'serializer_type' => $serializer_type
                    ],
                    $serializer_type
                )
            );
        } catch (ValidationException $ex1) {
            Log::warning($ex1);
            return $this->error412($ex1->getMessages());
        } catch (EntityNotFoundException $ex2) {
            Log::warning($ex2);
            return $this->error404(array('message' => $ex2->getMessage()));
        } catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }

    /**
     * @param $summit_id
     * @param $assistance_id
     * @return mixed
     */
    public function deleteSpeakerSummitAssistanceSummit($summit_id, $assistance_id)
    {
        try {

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->service->deleteSpeakerAssistance($summit, $assistance_id);

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
}