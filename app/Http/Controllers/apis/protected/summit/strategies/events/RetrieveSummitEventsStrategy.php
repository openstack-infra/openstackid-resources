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
    /**
     * @param array $params
     * @return PagingResponse
     * @throws ValidationException
     */
    public function getEvents(array $params = array())
    {
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


            return $this->retrieveEventsFromSource
            (
                new PagingInfo($page, $per_page), $this->buildFilter()
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
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @return PagingResponse
     */
    abstract public function retrieveEventsFromSource(PagingInfo $paging_info, Filter $filter = null);

    /**
     * @return array
     */
    protected function getValidFilters()
    {
        return array
        (
            'title'          => array('=@', '=='),
            'tags'           => array('=@', '=='),
            'start_date'     => array('>', '<', '<=', '>=', '=='),
            'end_date'       => array('>', '<', '<=', '>=', '=='),
            'summit_type_id' => array('=='),
            'event_type_id'  => array('=='),
        );
    }
}