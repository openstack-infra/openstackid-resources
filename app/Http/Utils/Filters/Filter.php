<?php namespace utils;

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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Filter
 * @package utils
 */
final class Filter
{
    /**
     * @var array
     */
    private $filters = array();

    /**
     * @var array
     */
    private $bindings = array();

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @param FilterElement $filter
     * @return $this
     */
    public function addFilterCondition(FilterElement $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * will return an array of filter elements, OR filters are returned on a sub array
     * @param string $field
     * @return null|FilterElement[]
     */
    public function getFilter($field)
    {
        $res = [];
        foreach ($this->filters as $filter) {

            if ($filter instanceof FilterElement && $filter->getField() === $field) {
                $res[] = $filter;
            }
            else if (is_array($filter)) {
                // OR
                $or_res = [];
                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && $e->getField() === $field) {
                        $or_res[] = $e;
                    }
                }
                if (count($or_res)) $res[] = $or_res;
            }
        }
        return $res;
    }

    /**
     * @param string $field
     * @return null|FilterElement
     */
    public function getUniqueFilter($field){
        $res = $this->getFilter($field);
        return count($res) == 1 ? $res[0]:null;
    }


    /**
     * @param string $field
     * @return bool
     */
    public function hasFilter($field){
        return count($this->getFilter($field)) > 0;
    }

    /**
     * @param string $field
     * @return null|FilterElement[]
     */
    public function getFlatFilter($field)
    {
        $res = array();
        foreach ($this->filters as $filter) {

            if ($filter instanceof FilterElement && $filter->getField() === $field) {
                $res[] = $filter;
            }
            else if (is_array($filter)) {
                // OR
                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && $e->getField() === $field) {
                        $res[] = $e;
                    }
                }

            }
        }
        return $res;
    }

    /**
     * @param Criteria $criteria
     * @param array $mappings
     * @return Criteria
     */
    public function apply2Criteria(Criteria $criteria, array $mappings)
    {
        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterElement) {
                if (isset($mappings[$filter->getField()])) {
                    $mapping = $mappings[$filter->getField()];

                    if ($mapping instanceof FilterMapping) {
                        continue;
                    }

                    $mapping = explode(':', $mapping);
                    $value   = $filter->getValue();

                    if (count($mapping) > 1) {
                        $value = $this->convertValue($value, $mapping[1]);
                    }
                    $criteria->andWhere(Criteria::expr()->eq($mapping[0], $value));
                }
            } else if (is_array($filter)) {
                // OR

                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && isset($mappings[$e->getField()])) {
                        $mapping = $mappings[$e->getField()];
                        if ($mapping instanceof FilterMapping) {
                            continue;
                        }
                        $mapping = explode(':', $mapping);
                        $value = $filter->getValue();
                        if (count($mapping) > 1) {
                            $value = $this->convertValue($value, $mapping[1]);
                        }
                        $criteria->orWhere(Criteria::expr()->eq($mapping[0], $value));

                    }
                }

            }
        }
        return $criteria;
    }

    /**
     * @param QueryBuilder $query
     * @param array $mappings
     * @return $this
     */
    public function apply2Query(QueryBuilder $query, array $mappings)
    {
        $param_prefix = "param_%s";
        $param_idx    = 1;
        $bindings     = [];

        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterElement && isset($mappings[$filter->getField()])) {
                $mapping = $mappings[$filter->getField()];

                if ($mapping instanceof DoctrineJoinFilterMapping) {
                    $query = $mapping->apply($query, $filter);
                    continue;
                }
                if ($mapping instanceof DoctrineSwitchFilterMapping) {
                    $query = $mapping->apply($query, $filter);
                    continue;
                }
                if ($mapping instanceof DoctrineFilterMapping) {
                    $query = $mapping->apply($query, $filter);
                    continue;
                }
                if ($mapping instanceof DoctrineInstanceOfFilterMapping) {
                    $query = $mapping->apply($query, $filter);
                    continue;
                }
                else if(is_array($mapping)){
                    $condition = '';
                    foreach ($mapping as $mapping_or){
                        $mapping_or = explode(':', $mapping_or);
                        $value      = $filter->getValue();
                        if (count($mapping_or) > 1) {
                            $value = $this->convertValue($value, $mapping_or[1]);
                        }

                        if(!empty($condition)) $condition .= ' OR ';
                        $bindings[sprintf($param_prefix, $param_idx)] = $value;
                        $condition .= sprintf("%s %s :%s", $mapping_or[0], $filter->getOperator(), sprintf($param_prefix, $param_idx));
                        ++$param_idx;
                    }
                    $query->andWhere($condition);
                }
                else {
                    $mapping = explode(':', $mapping);
                    $value   = $filter->getValue();

                    if (count($mapping) > 1) {
                        $value = $this->convertValue($value, $mapping[1]);
                    }
                    $bindings[sprintf($param_prefix, $param_idx)] = $value;
                    $query = $query->andWhere(sprintf("%s %s :%s", $mapping[0], $filter->getOperator(), sprintf($param_prefix, $param_idx)));
                    ++$param_idx;
                }
            }
            else if (is_array($filter)) {
                // OR
                $sub_or_query = '';
                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && isset($mappings[$e->getField()])) {

                        $mapping = $mappings[$e->getField()];
                        if ($mapping instanceof DoctrineJoinFilterMapping) {
                            $condition = $mapping->applyOr($query, $e);
                            if(!empty($sub_or_query)) $sub_or_query .= ' OR ';
                            $sub_or_query .= $condition;
                            continue;
                        }
                        if ($mapping instanceof DoctrineSwitchFilterMapping) {
                            $condition = $mapping->applyOr($query, $e);
                            if(!empty($sub_or_query)) $sub_or_query .= ' OR ';
                            $sub_or_query .= $condition;
                            continue;
                        }
                        if ($mapping instanceof DoctrineFilterMapping) {
                            $condition = $mapping->applyOr($query, $e);
                            if(!empty($sub_or_query)) $sub_or_query .= ' OR ';
                            $sub_or_query .= $condition;
                            continue;
                        }
                        if ($mapping instanceof DoctrineInstanceOfFilterMapping) {
                            $condition = $mapping->applyOr($query, $e);
                            if(!empty($sub_or_query)) $sub_or_query .= ' OR ';
                            $sub_or_query .= $condition;
                            continue;
                        }
                        else if(is_array($mapping)){
                            $condition = '';
                            foreach ($mapping as $mapping_or){
                                $mapping_or = explode(':', $mapping_or);
                                $value      = $e->getValue();
                                if (count($mapping_or) > 1) {
                                    $value = $this->convertValue($value, $mapping_or[1]);
                                }

                                if(!empty($condition)) $condition .= ' OR ';
                                $bindings[sprintf($param_prefix, $param_idx)] = $value;
                                $condition .= sprintf(" %s %s :%s ", $mapping_or[0], $e->getOperator(), sprintf($param_prefix, $param_idx));
                                ++$param_idx;
                            }
                            if(!empty($sub_or_query)) $sub_or_query .= ' OR ';
                            $sub_or_query .= ' ( '.$condition.' ) ';
                        }
                        else {
                            $mapping = explode(':', $mapping);
                            $value = $e->getValue();

                            if (count($mapping) > 1) {
                                $value = $this->convertValue($value, $mapping[1]);
                            }

                            if(!empty($sub_or_query)) $sub_or_query .= ' OR ';

                            $bindings[sprintf($param_prefix, $param_idx)] = $value;
                            $sub_or_query .= sprintf(" %s %s :%s ", $mapping[0], $e->getOperator(), sprintf($param_prefix, $param_idx));
                            ++$param_idx;
                        }
                    }
                }
                $query->andWhere($sub_or_query);
            }
        }
        foreach($bindings as $param => $value)
            $query->setParameter($param, $value);
        return $this;
    }

    /**
     * @param string $value
     * @param string $original_format
     * @return mixed
     */
    private function convertValue($value, $original_format)
    {
        switch ($original_format) {
            case 'datetime_epoch':
                $datetime = new \DateTime("@$value");
                return sprintf("%s", $datetime->format("Y-m-d H:i:s"));
                break;
            case 'json_int':
                return intval($value);
                break;
            case 'json_string':
                return sprintf("%s",$value);
                break;
            default:
                return $value;
                break;
        }
    }

    /**
     * @return array
     */
    public function getSQLBindings()
    {
        return $this->bindings;
    }

    /**
     * @param array $mappings
     * @return string
     */
    public function toRawSQL(array $mappings)
    {
        $sql            = '';
        $this->bindings = [];
        $param_prefix   = "param_%s";
        $param_idx      = 1;

        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterElement) {
                if (isset($mappings[$filter->getField()])) {

                    $mapping = $mappings[$filter->getField()];
                    $mapping = explode(':', $mapping);
                    $value   = $filter->getValue();
                    $op      = $filter->getOperator();
                    if (count($mapping) > 1) {
                        $filter->setValue($this->convertValue($value, $mapping[1]));
                    }
                    $cond = sprintf(' %s %s :%s', $mapping[0], $op, sprintf($param_prefix, $param_idx));
                    $this->bindings[sprintf($param_prefix, $param_idx)] = $filter->getValue();
                    ++$param_idx;
                    if (!empty($sql)) $sql .= " AND ";
                    $sql .= $cond;
                }
            } else if (is_array($filter)) {
                // OR
                $sql .= " ( ";
                $sql_or = '';
                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && isset($mappings[$e->getField()])) {
                        $mapping = $mappings[$e->getField()];
                        $mapping = explode(':', $mapping);
                        $value   = $e->getValue();
                        $op      = $e->getOperator();
                        if (count($mapping) > 1) {
                            $e->setValue($this->convertValue($value, $mapping[1]));
                        }
                        $cond = sprintf(" %s %s :%s", $mapping[0], $op, sprintf($param_prefix, $param_idx));
                        $this->bindings[sprintf($param_prefix, $param_idx)] = $e->getValue();
                        ++$param_idx;
                        if (!empty($sql_or)) $sql_or .= " OR ";
                        $sql_or .= $cond;
                    }
                }
                $sql .= $sql_or . " ) ";
            }
        }
        return $sql;
    }

    /**
     * @param string $field
     * @return array
     */
    public function getFilterCollectionByField($field){
        $list   = [];
        $filter = $this->getFilter($field);

        if(is_array($filter)){
            if(is_array($filter[0])){
                foreach ($filter[0] as $filter_element)
                    $list[] = intval($filter_element->getValue());
            }
            else{
                $list[] = intval($filter[0]->getValue());
            }
        }
        return $list;
    }
}