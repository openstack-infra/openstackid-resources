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
namespace models\utils;

use Eloquent;
use ReflectionClass;

/**
 * Class BaseModelEloquent
 */
class BaseModelEloquent extends Eloquent {


    private $class = null;
    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters){
        foreach($filters as $filter){
            $query = $query->where($filter['name'],$filter['op'], $filter['value']);
        }
        return $query;
    }

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->class  = new ReflectionClass(get_class($this));
        if ($this->useSti()) {
            $this->setAttribute($this->stiClassField, $this->class->getName());
        }
    }

    private function useSti() {
        return ($this->stiClassField && $this->stiBaseClass);
    }

    public function newQuery($excludeDeleted = true)
    {
        $builder = parent::newQuery($excludeDeleted);
        // If I am using STI, and I am not the base class,
        // then filter on the class name.
        if ($this->useSti() && get_class(new $this->stiBaseClass) !== get_class($this)) {
            $builder->where($this->stiClassField, "=", $this->class->getShortName());
        }
        return $builder;
    }

    public function newFromBuilder($attributes = array(), $connection = NULL)
    {
        if ($this->useSti() && $attributes->{$this->stiClassField}) {
            $class = $this->class->getName();
            $instance = new $class;
            $instance->exists = true;
            $instance->setRawAttributes((array) $attributes, true);
            return $instance;
        } else {
            return parent::newFromBuilder($attributes, $connection);
        }
    }
}