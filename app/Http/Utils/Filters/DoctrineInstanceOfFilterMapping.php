<?php namespace utils;
/**
 * Copyright 2018 OpenStack Foundation
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
 * Class DoctrineInstanceOfFilterMapping
 * @package utils
 */
final class DoctrineInstanceOfFilterMapping extends FilterMapping
{

    private $class_names = [];

    public function __construct($alias, $class_names = [])
    {
        $this->class_names = $class_names;
        parent::__construct($alias, sprintf("%s %s :class_name", $alias, self::InstanceOfDoctrine));
    }

    /**
     * @param FilterElement $filter
     * @throws \Exception
     */
    public function toRawSQL(FilterElement $filter)
    {
        throw new \Exception;
    }

    const InstanceOfDoctrine = 'INSTANCE OF';

    private function translateClassName($value){
        if(isset($this->class_names[$value])) return $this->class_names[$value];
        return $value;
    }
    /**
     * @param QueryBuilder $query
     * @param FilterElement $filter
     * @return QueryBuilder
     */
    public function apply(QueryBuilder $query, FilterElement $filter){
        $where = str_replace(":class_name", $this->translateClassName($filter->getValue()), $this->where);
        return $query->andWhere($where);
    }

    /**
     * @param QueryBuilder $query
     * @param FilterElement $filter
     * @return string
     */
    public function applyOr(QueryBuilder $query, FilterElement $filter){
        $where = str_replace(":class_name", $this->translateClassName($filter->getValue()), $this->where);
        return $where;
    }

}