<?php namespace utils;
/**
 * Copyright 2017 OpenStack Foundation
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
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DoctrineFilterMapping
 * @package utils
 */
class DoctrineFilterMapping extends FilterMapping
{

    /**
     * DoctrineFilterMapping constructor.
     * @param string $condition
     */
    public function __construct($condition)
    {
        parent::__construct("", $condition);
    }

    /**
     * @param FilterElement $filter
     * @return string
     */
    public function toRawSQL(FilterElement $filter)
    {
        throw new \Exception;
    }

    /**
     * @param QueryBuilder $query
     * @param FilterElement $filter
     * @return QueryBuilder
     */
    public function apply(QueryBuilder $query, FilterElement $filter){
        $where = str_replace(":value", $filter->getValue(), $this->where);
        $where = str_replace(":operator", $filter->getOperator(), $where);
        return $query->andWhere($where);
    }

    /**
     * @param QueryBuilder $query
     * @param FilterElement $filter
     * @return string
     */
    public function applyOr(QueryBuilder $query, FilterElement $filter){
        $where = str_replace(":value", $filter->getValue(), $this->where);
        $where = str_replace(":operator", $filter->getOperator(), $where);
        return $where;
    }
}