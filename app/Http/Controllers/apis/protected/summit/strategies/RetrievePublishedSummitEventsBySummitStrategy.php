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

use utils\Filter;

/**
 * Class RetrievePublishedSummitEventsBySummitStrategy
 * @package App\Http\Controllers
 */
final class RetrievePublishedSummitEventsBySummitStrategy extends RetrieveSummitEventsBySummitStrategy
{

    /**
     * @param $page
     * @param $per_page
     * @param Filter|null $filter
     * @return array
     */
    public function retrieveEventsFromSource($page, $per_page, Filter $filter = null)
    {
        return $this->summit->schedule($page, $per_page, $filter);
    }
}