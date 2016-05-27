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

use models\summit\ISummitEventRepository;
use utils\Filter;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class RetrieveAllSummitEventsStrategy
 * @package App\Http\Controllers
 */
class RetrieveAllSummitEventsStrategy extends RetrieveSummitEventsStrategy
{
    /**
     * @var ISummitEventRepository
     */
    protected $event_repository;

    /**
     * RetrieveAllSummitEventsStrategy constructor.
     * @param ISummitEventRepository $event_repository
     */
    public function __construct(ISummitEventRepository $event_repository)
    {
        $this->event_repository = $event_repository;
    }

    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @return PagingResponse
     */
    public function retrieveEventsFromSource(PagingInfo $paging_info, Filter $filter = null)
    {
        return $this->event_repository->getAllByPage($paging_info, $filter);
    }

    /**
     * @return array
     */
    protected function getValidFilters()
    {
        $valid_filters = parent::getValidFilters();
        $valid_filters['summit_id'] = array('==');
        return $valid_filters;
    }
}