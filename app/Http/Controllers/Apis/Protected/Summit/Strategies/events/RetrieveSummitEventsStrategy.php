<?php namespace App\Http\Controllers;
/**
 * Copyright 2015 OpenStack Foundation
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
use models\exceptions\ValidationException;
use utils\Filter;
use utils\Order;
use utils\OrderParser;
use utils\PagingResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use utils\FilterParser;
use utils\PagingInfo;
/**
 * Class RetrieveSummitEventsStrategy
 * @package App\Http\Controllers
 */
abstract class RetrieveSummitEventsStrategy
{

    protected function getPageParams(){
        // default values
        $page     = 1;
        $per_page = 5;

        if (Input::has('page')) {
            $page     = intval(Input::get('page'));
            $per_page = intval(Input::get('per_page'));
        }
        return [$page, $per_page];
    }

    /**
     * @param array $params
     * @return PagingResponse
     * @throws ValidationException
     */
    public function getEvents(array $params = [])
    {
            $values = Input::all();

            $rules = [
                'page'     => 'integer|min:1',
                'per_page' => 'required_with:page|integer|min:5|max:100',
            ];

            $validation = Validator::make($values, $rules);

            if ($validation->fails()) {
                $ex = new ValidationException();
                throw $ex->setMessages($validation->messages()->toArray());
            }


            list($page, $per_page) = $this->getPageParams();

            return $this->retrieveEventsFromSource
            (
                new PagingInfo($page, $per_page), $this->buildFilter(), $this->buildOrder()
            );
    }

    /**
     * @return null|Filter
     */
    protected function buildFilter(){
        $filter = null;
        if (Input::has('filter')) {
            $filter = FilterParser::parse(Input::get('filter'), $this->getValidFilters());
        }
        return $filter;
    }

    /**
     * @return null|Order
     */
    protected function buildOrder(){
        $order = null;
        if (Input::has('order'))
        {
            $order = OrderParser::parse(Input::get('order'), [

                'title',
                'start_date',
                'end_date',
                'id',
                'created',
            ]);
        }
        return $order;
    }
    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    abstract public function retrieveEventsFromSource(PagingInfo $paging_info, Filter $filter = null, Order $order = null);

    /**
     * @return array
     */
    protected function getValidFilters()
    {
        return [

            'title'            => ['=@', '=='],
            'abstract'         => ['=@', '=='],
            'social_summary'   => ['=@', '=='],
            'tags'             => ['=@', '=='],
            'start_date'       => ['>', '<', '<=', '>=', '=='],
            'end_date'         => ['>', '<', '<=', '>=', '=='],
            'summit_type_id'   => ['=='],
            'event_type_id'    => ['=='],
            'track_id'         => ['=='],
            'speaker_id'       => ['=='],
            'location_id'      => ['=='],
            'speaker'          => ['=@', '=='],
            'speaker_email'    => ['=@', '=='],
            'selection_status' => ['=='],
            'id'               => ['=='],
        ];
    }
}