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
use App\Models\Foundation\Summit\Repositories\ITrackTagGroupAllowedTagsRepository;
use models\oauth2\IResourceServerContext;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use utils\Filter;
use utils\FilterParser;
use utils\FilterParserException;
use utils\OrderParser;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use utils\PagingInfo;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\ISummitRepository;
/**
 * Class OAuth2SummitTrackTagGroupsApiController
 * @package App\Http\Controllers
 */
final class OAuth2SummitTrackTagGroupsApiController extends OAuth2ProtectedController
{
    /**
     * @var ISummitRepository
     */
    private $summit_repository;

    /**
     * OAuth2SummitTrackTagGroupsApiController constructor.
     * @param ISummitRepository $summit_repository
     * @param ITrackTagGroupAllowedTagsRepository $repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ITrackTagGroupAllowedTagsRepository $repository,
        IResourceServerContext $resource_server_context
    )
    {
        parent::__construct($resource_server_context);
        $this->summit_repository  = $summit_repository;
        $this->repository = $repository;
    }

    public function getAllowedTags($summit_id){

        $values = Input::all();

        $rules = array
        (
            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        );

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
                $filter = FilterParser::parse(Input::get('filter'),  array
                (
                    'tag' => ['=@', '=='],
                ));
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'tag',
                    'id',
                ));
            }

            if(is_null($filter)) $filter = new Filter();

            $data      = $this->repository->getBySummit($summit, new PagingInfo($page, $per_page), $filter, $order);
            $fields    = Request::input('fields', '');
            $fields    = !empty($fields) ? explode(',', $fields) : [];
            $relations = Request::input('relations', '');
            $relations = !empty($relations) ? explode(',', $relations) : [];

            return $this->ok
            (
                $data->toArray
                (
                    Request::input('expand', ''),
                    $fields,
                    $relations
                )
            );
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
        catch (\Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}