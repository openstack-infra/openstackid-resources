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
     * @param string $field
     * @return null|FilterElement[]
     */
    public function getFilter($field)
    {
        $res = array();
        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterElement && $filter->getField() === $field) {
                $res[] = $filter;
            } else if (is_array($filter)) {
                // OR
                $or_res = array();
                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && $e->getField() === $field) {
                        array_push($or_res, $e);
                    }
                }
                if (count($or_res)) array_push($res, $or_res);
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
                    $value = $filter->getValue();
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
        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterElement && isset($mappings[$filter->getField()])) {
                $mapping = $mappings[$filter->getField()];
                if ($mapping instanceof DoctrineJoinFilterMapping) {
                    $query = $mapping->apply($query, $filter);
                    continue;
                }

                $mapping = explode(':', $mapping);
                $value = $filter->getValue();
                if (count($mapping) > 1) {
                    $value = $this->convertValue($value, $mapping[1]);
                }
                $query = $query->where($mapping[0] . ' ' . $filter->getOperator() . ' ' . $value);
            }
            else if (is_array($filter)) {
                // OR
                foreach ($filter as $e) {
                    if ($e instanceof FilterElement && isset($mappings[$e->getField()])) {
                        $mapping = $mappings[$e->getField()];

                        if ($mapping instanceof DoctrineJoinFilterMapping) {
                            $query = $mapping->applyOr($query, $e);
                            continue;
                        }

                        $mapping = explode(':', $mapping);
                        $value = $filter->getValue();
                        if (count($mapping) > 1) {
                            $value = $this->convertValue($value, $mapping[1]);
                        }
                        $query->orWhere($mapping[0], $e->getOperator(), $value);
                    }
                }
            }
        }
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
                return sprintf("'%s'", $datetime->format("Y-m-d H:i:s"));
                break;
            case 'json_int':
                return intval($value);
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
        $sql = '';
        $this->bindings = array();

        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterElement) {
                if (isset($mappings[$filter->getField()])) {
                    $mapping = $mappings[$filter->getField()];
                    $mapping = explode(':', $mapping);
                    $value = $filter->getValue();
                    $op = $filter->getOperator();
                    if (count($mapping) > 1) {
                        $filter->setValue($this->convertValue($value, $mapping[1]));
                    }
                    $cond = sprintf(' %s %s :%s', $mapping[0], $op, $filter->getField());
                    $this->bindings[$filter->getField()] = $filter->getValue();
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
                        $value = $e->getValue();
                        $op = $e->getOperator();
                        if (count($mapping) > 1) {
                            $e->setValue($this->convertValue($value, $mapping[1]));
                        }
                        $cond = sprintf(" %s %s :%s", $mapping[0], $op, $e->getField());
                        $this->bindings[$e->getField()] = $e->getValue();
                        if (!empty($sql_or)) $sql_or .= " OR ";
                        $sql_or .= $cond;
                    }
                }
                $sql .= $sql_or . " ) ";
            }
        }
        return $sql;
    }
}