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
 * Class DoctrineSwitchFilterMapping
 * @package utils
 */
class DoctrineSwitchFilterMapping extends FilterMapping
{
    /**
     * @var DoctrineCaseFilterMapping[]
     */
    private $case_statements;

    public function __construct($case_statements = [])
    {
        parent::__construct("", "");
        $this->case_statements = $case_statements;
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
        if(!isset($this->case_statements[$filter->getValue()])) return $query;
        $case_statement = $this->case_statements[$filter->getValue()];
        return $query->andWhere($case_statement->getCondition());
    }

    /**
     * @param QueryBuilder $query
     * @param FilterElement $filter
     * @return string
     */
    public function applyOr(QueryBuilder $query, FilterElement $filter){
        if(!isset($this->case_statements[$filter->getValue()])) return $query;
        $case_statement = $this->case_statements[$filter->getValue()];
        return $case_statement->getCondition();
    }
}