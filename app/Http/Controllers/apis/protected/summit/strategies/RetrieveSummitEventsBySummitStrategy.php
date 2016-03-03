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

use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\ISummitRepository;
use models\summit\Summit;
use utils\PagingResponse;

/**
 * Class RetrieveSummitEventsBySummitStrategy
 * @package App\Http\Controllers
 */
abstract class RetrieveSummitEventsBySummitStrategy extends RetrieveSummitEventsStrategy
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
     * RetrieveSummitEventsBySummitStrategy constructor.
     * @param ISummitRepository $summit_repository
     */
    public function __construct(ISummitRepository $summit_repository)
    {
        $this->summit_repository = $summit_repository;
    }

    /**
     * @param array $params
     * @return PagingResponse
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function getEvents(array $params = array())
    {
        $summit_id = isset($params['summit_id'])? $params['summit_id']:0;
        $this->summit = SummitFinderStrategyFactory::build($this->summit_repository)->find($summit_id);
        if (is_null($this->summit)) throw new EntityNotFoundException;
        return parent::getEvents($params);
    }

}