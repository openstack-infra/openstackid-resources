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

namespace utils;

/**
 * Class FilterMapping
 * @package utils
 */
abstract class FilterMapping
{
    /**
     * @var string
     */
    protected $table;
    /**
     * @var string
     */
    protected $where;

    /**
     * FilterMapping constructor.
     * @param string $table
     * @param string $where
     */
    public function __construct($table, $where)
    {
        $this->table = $table;
        $this->where = $where;
    }

    /**
     * @param FilterElement $filter
     * @return string
     */
    public abstract function toRawSQL(FilterElement $filter);
}