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
use App\Models\Foundation\Summit\Repositories\ISummitTrackRepository;
use App\Services\Model\ISummitTrackService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use models\exceptions\EntityNotFoundException;
use ModelSerializers\SerializerRegistry;
use utils\Filter;
use utils\FilterParser;
use utils\OrderParser;
use utils\PagingInfo;
use Exception;
/**
 * Class OAuth2SummitTracksApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitTracksApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * @var ISummitTrackService
     */
    private $track_service;

    /**
     * OAuth2SummitsEventTypesApiController constructor.
     * @param ISummitTrackRepository $repository
     * @param ISummitRepository $summit_repository
     * @param ISummitTrackService $track_service
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitTrackRepository $repository,
        ISummitRepository $summit_repository,
        ISummitTrackService $track_service,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->repository         = $repository;
        $this->summit_repository  = $summit_repository;
        $this->track_service      = $track_service;
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
                    'title'       => ['=@', '=='],
                    'description' => ['=@', '=='],
                    'code'        => ['=@', '=='],
                    'group_name'  => ['=@', '=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'code',
                    'title'
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

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
        $rules  = [];

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
                    'title'       => ['=@', '=='],
                    'description' => ['=@', '=='],
                    'code'        => ['=@', '=='],
                    'group_name'  => ['=@', '=='],
                ]);
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), [

                    'id',
                    'code',
                    'title'
                ]);
            }

            if(is_null($filter)) $filter = new Filter();

            $data = $this->repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);

            $filename = "tracks-" . date('Ymd');
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
     * @param $track_id
     * @return mixed
     */
    public function getTrackBySummit($summit_id, $track_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();
            $track = $summit->getPresentationCategory($track_id);
            if(is_null($track))
                return $this->error404();
            return $this->ok(SerializerRegistry::getInstance()->getSerializer($track)->serialize(Request::input('expand', '')));
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
    public function addTrackBySummit($summit_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = [
                'title'                     => 'required|string|max:50',
                'description'               => 'required|string|max:500',
                'code'                      => 'sometimes|string|max:5',
                'session_count'             => 'sometimes|integer',
                'alternate_count'           => 'sometimes|integer',
                'lightning_count'           => 'sometimes|integer',
                'lightning_alternate_count' => 'sometimes|integer',
                'voting_visible'            => 'sometimes|boolean',
                'chair_visible'             => 'sometimes|boolean',
            ];

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $track = $this->track_service->addTrack($summit, $data->all());

            return $this->created(SerializerRegistry::getInstance()->getSerializer($track)->serialize());
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
     * @param $track_id
     * @return mixed
     */
    public function updateTrackBySummit($summit_id, $track_id){
        try {
            if(!Request::isJson()) return $this->error400();
            $data = Input::json();

            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $rules = [
                'title'                     => 'sometimes|string|max:50',
                'description'               => 'sometimes|string|max:500',
                'code'                      => 'sometimes|string|max:5',
                'session_count'             => 'sometimes|integer',
                'alternate_count'           => 'sometimes|integer',
                'lightning_count'           => 'sometimes|integer',
                'lightning_alternate_count' => 'sometimes|integer',
                'voting_visible'            => 'sometimes|boolean',
                'chair_visible'             => 'sometimes|boolean',
            ];

            // Creates a Validator instance and validates the data.
            $validation = Validator::make($data->all(), $rules);

            if ($validation->fails()) {
                $messages = $validation->messages()->toArray();

                return $this->error412
                (
                    $messages
                );
            }

            $track = $this->track_service->updateTrack($summit, $track_id, $data->all());

            return $this->updated(SerializerRegistry::getInstance()->getSerializer($track)->serialize());
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
     * @param $track_id
     * @return mixed
     */
    public function deleteTrackBySummit($summit_id, $track_id){
        try {
            $summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
            if (is_null($summit)) return $this->error404();

            $this->track_service->deleteTrack($summit, $track_id);

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
}