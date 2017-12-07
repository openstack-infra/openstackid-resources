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
use App\Http\Utils\FilterAvailableSummitsStrategy;
use models\oauth2\IResourceServerContext;
use models\summit\ISummitRepository;
use models\summit\Summit;
/**
 * Class CurrentSummitFinderStrategy
 * @package App\Http\Controllers
 */
class CurrentSummitFinderStrategy implements ISummitFinderStrategy
{

    /**
     * @var ISummitRepository
     */
    private $repository;

    /**
     * @var IResourceServerContext
     */
    private $resource_server_ctx;

    /**
     * CurrentSummitFinderStrategy constructor.
     * @param ISummitRepository $repository
     * @param IResourceServerContext $resource_server_ctx
     */
    public function __construct
    (
        ISummitRepository $repository,
        IResourceServerContext $resource_server_ctx
    )
    {
        $this->resource_server_ctx = $resource_server_ctx;
        $this->repository          = $repository;
    }

    /**
     * @param mixed $summit_id
     * @return null|Summit
     */
    public function find($summit_id)
    {
        $summit = $summit_id === 'current' ? $this->repository->getCurrent() : $this->repository->getById(intval($summit_id));
        if(is_null($summit)) return null;
        $show_all = FilterAvailableSummitsStrategy::shouldReturnAllSummits($this->resource_server_ctx);
        if($show_all) return $summit;
        if(!$summit->isAvailableOnApi()) return null;
        return $summit;
    }
}