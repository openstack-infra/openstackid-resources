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
use models\utils\IEntity;
use ModelSerializers\SerializerRegistry;
use Request;

/**
 * Class PagingResponse
 * @package utils
 */
final class PagingResponse
{
    /**
     * @var int
     */
    private $total;
    /**
     * @var int
     */
    private $per_page;
    /**
     * @var int
     */
    private $page;
    /**
     * @var int
     */
    private $last_page;
    /**
     * @var array
     */
    private $items;

    /**
     * @param int $total
     * @param int $per_page
     * @param int $page
     * @param int $last_page
     * @param array $items
     */
    public function __construct($total, $per_page, $page, $last_page, array $items)
    {
        $this->total     = $total;
        $this->per_page  = $per_page;
        $this->page      = $page;
        $this->last_page = $last_page;
        $this->items     = $items;
    }

    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLastPage()
    {
        return $this->last_page;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function toArray($expand = null, array $fields = [], array $relations = [], array $params = [] )
    {
        $items = [];
        foreach($this->items as $i)
        {
            if($i instanceof IEntity)
            {
                $i = SerializerRegistry::getInstance()->getSerializer($i)->serialize($expand, $fields, $relations, $params);
            }
            $items[] = $i;
        }

        return array
        (
            'total'        =>  $this->total,
            'per_page'     =>  $this->per_page,
            'current_page' =>  $this->page,
            'last_page'    =>  $this->last_page,
            'data'         =>  $items,
        );
    }
}