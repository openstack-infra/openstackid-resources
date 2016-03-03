<?php
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

namespace App\Http\Controllers;

use models\exceptions\ValidationException;
use Request;
use utils\Filter;
use utils\PagingResponse;
use Validator;
use Input;
use Log;
use utils\FilterParser;

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

            $expand = Request::input('expand', '');

            // default values
            $page     = 1;
            $per_page = 5;

            if (Input::has('page')) {
                $page = intval(Input::get('page'));
                $per_page = intval(Input::get('per_page'));
            }

            $filter = null;
            if (Input::has('filter')) {
                $filter = FilterParser::parse(Input::get('filter'), $this->getValidFilters());
            }

            $events = array();

            list($total, $per_page, $current_page, $last_page, $items) = $this->retrieveEventsFromSource
            (
                $page, $per_page, $filter
            );

            foreach ($items as $event) {
                $data = $event->toArray();
                if (!empty($expand)) {
                    foreach (explode(',', $expand) as $relation) {
                        switch (trim($relation)) {
                            case 'feedback': {
                                $feedback = array();
                                list($total2, $per_page2, $current_page2, $last_page2, $items2) = $event->feedback(1,
                                    PHP_INT_MAX);
                                foreach ($items2 as $f) {
                                    array_push($feedback, $f->toArray());
                                }
                                $data['feedback'] = $feedback;
                            }
                            break;
                            case 'location': {
                                $location         = $event->getLocation();
                                $data['location'] = $location->toArray();
                                unset($data['location_id']);
                            }
                            break;
                        }
                    }
                }
                array_push($events, $data);
            }

            return new PagingResponse
            (
                $total,
                $per_page,
                $current_page,
                $last_page,
                $events
            );
        }

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter $filter
     * @return array
     */
    abstract public function retrieveEventsFromSource($page, $per_page, Filter $filter);

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