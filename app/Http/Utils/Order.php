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

use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class Order
 * @package utils
 */
final class Order
{
    /**
     * @var array
     */
    private $ordering;

    public function __construct($ordering = array())
    {
        $this->ordering = $ordering;
    }

    public function apply2Relation(Relation $relation, array $mappings)
    {
        foreach ($this->ordering as $order) {
            if ($order instanceof OrderElement) {
                if (isset($mappings[$order->getField()])) {
                    $mapping = $mappings[$order->getField()];
                    $relation->getQuery()->orderBy($mapping, $order->getDirection());
                }
            }
        }

        return $this;
    }

    /**
     * @param array $mappings
     * @return string
     */
    public function toRawSQL(array $mappings)
    {
        $sql = ' ORDER BY ';
        foreach ($this->ordering as $order) {
            if ($order instanceof OrderElement) {
                if (isset($mappings[$order->getField()])) {
                    $mapping = $mappings[$order->getField()];
                    $sql .= sprintf('%s %s, ', $mapping, $order->getDirection());
                }
            }
        }
        return substr($sql, 0 , strlen($sql) - 2);
    }
}