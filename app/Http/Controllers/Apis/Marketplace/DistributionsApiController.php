<?php namespace App\Http\Controllers;
/**
 * Copyright 2017 OpenStack Foundation
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
use App\Http\Controllers\JsonController;
use App\Models\Foundation\Marketplace\IDistributionRepository;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use utils\FilterParser;
use Illuminate\Support\Facades\Input;
use utils\Filter;
use utils\PagingInfo;
use Illuminate\Support\Facades\Request;
use utils\OrderParser;
/**
 * Class DistributionsApiController
 * @package App\Http\Controllers\Apis\Marketplace
 */
class DistributionsApiController extends JsonController
{
    /**
     * @var IDistributionRepository
     */
    private $repository;

    /**
     * DistributionsApiController constructor.
     * @param IDistributionRepository $repository
     */
    public function __construct(IDistributionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return mixed
     */
    public function getDistributions(){
        $values = Input::all();

        $rules = array
        (
            'page'     => 'integer|min:1',
            'per_page' => 'required_with:page|integer|min:5|max:100',
        );

        try {

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
                    'name'    => ['=@', '=='],
                    'company' => ['=@', '=='],
                ));
            }

            $order = null;

            if (Input::has('order'))
            {
                $order = OrderParser::parse(Input::get('order'), array
                (
                    'name',
                    'company',
                    'id',
                ));
            }

            if(is_null($filter)) $filter = new Filter();

            $data      = $this->repository->getAllByPage(new PagingInfo($page, $per_page), $filter, $order);
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
        catch (Exception $ex) {
            Log::error($ex);
            return $this->error500($ex);
        }
    }
}