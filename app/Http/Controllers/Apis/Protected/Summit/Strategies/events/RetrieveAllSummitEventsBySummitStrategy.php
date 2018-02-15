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

use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitEventRepository;
use models\summit\ISummitRepository;
use models\summit\Summit;
use utils\Filter;
use utils\FilterParser;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;

/**
 * Class RetrieveSummitEventsBySummitStrategy
 * @package App\Http\Controllers
 */
class RetrieveAllSummitEventsBySummitStrategy extends RetrieveSummitEventsStrategy
{
    /**
     * @var ISummitRepository
     */
    protected $summit_repository;

    /**
     * @var Summit
     */
    protected $summit;

    /**
     * @var IResourceServerContext
     */
    protected $resource_server_context;

    /**
     * @var ISummitEventRepository
     */
    protected $events_repository;

    /**
     * RetrieveAllSummitEventsBySummitStrategy constructor.
     * @param ISummitRepository $summit_repository
     * @param ISummitEventRepository $events_repository
     * @param IResourceServerContext $resource_server_context
     */
    public function __construct
    (
        ISummitRepository $summit_repository,
        ISummitEventRepository $events_repository,
        IResourceServerContext $resource_server_context
    )
    {
        $this->events_repository       = $events_repository;
        $this->summit_repository       = $summit_repository;
        $this->resource_server_context = $resource_server_context;
    }

    /**
     * @param array $params
     * @return PagingResponse
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function getEvents(array $params = [])
    {
        $summit_id    = isset($params['summit_id'])? $params['summit_id']:0;
        $this->summit = SummitFinderStrategyFactory::build($this->summit_repository, $this->resource_server_context)->find($summit_id);
        if (is_null($this->summit)) throw new EntityNotFoundException('summit not found!');

        return parent::getEvents($params);
    }

    /**
     * @return array
     */
    protected function getValidFilters()
    {
        $valid_filters = parent::getValidFilters();
        $valid_filters['summit_id'] = ['=='];
        $valid_filters['published'] = ['=='];
        return $valid_filters;
    }

    /**
     * @return null|Filter
     */
    protected function buildFilter(){
        $filter = parent::buildFilter();

        if(is_null($filter))
        {
            $filter = new Filter([]);
        }
        $filter->addFilterCondition(FilterParser::buildFilter('summit_id','==',$this->summit->getId()));
        return $filter;
    }


    /**
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function retrieveEventsFromSource(PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {
        return $this->events_repository->getAllByPage($paging_info, $filter, $order);
    }

}