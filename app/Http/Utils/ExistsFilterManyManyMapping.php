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
 * Class ExistsFilterManyManyMapping
 * @package utils
 */
class ExistsFilterManyManyMapping extends JoinFilterMapping
{
    /**
     * @var string
     */
    private $pivot_table;

    /**
     * ExistsFilterManyManyMapping constructor.
     * @param string $table
     * @param string $pivot_table
     * @param string $join
     * @param string $where
     */
    public function __construct($table, $pivot_table, $join, $where)
    {
        parent::__construct($table, $join, $where);
        $this->pivot_table = $pivot_table;
    }

    /**
     * @param FilterElement $filter
     * @return string
     */
    public function toRawSQL(FilterElement $filter)
    {
        $where = str_replace(":value", $filter->getValue(), $this->where);
        $where = str_replace(":operator", $filter->getOperator(), $where);

        $sql = <<<SQL
      EXISTS ( SELECT 1 FROM {$this->table} INNER JOIN {$this->pivot_table} ON {$this->join} WHERE {$where} )
SQL;
        return $sql;
    }
}