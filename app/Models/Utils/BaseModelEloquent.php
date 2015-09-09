<?php namespace models\utils;

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

use DB;
use Eloquent;
use ReflectionClass;

/**
 * Class BaseModelEloquent
 */
class BaseModelEloquent extends Eloquent
{

    private $class = null;

    protected $array_mappings = array();

    /**
     * @param $query
     * @param array $filters
     * @return mixed
     */
    public function scopeFilter($query, array $filters)
    {
        foreach ($filters as $filter) {
            $query = $query->where($filter['name'], $filter['op'], $filter['value']);
        }

        return $query;
    }

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->class = new ReflectionClass(get_class($this));
        if ($this->useSti()) {
            $this->setAttribute($this->stiClassField, $this->class->getName());
        }
    }

    public function toArray()
    {
        $values = parent::toArray();

        if (count($this->array_mappings)) {
            $new_values = array();
            foreach ($this->array_mappings as $old_key => $new_key) {
                $value = $values[$old_key];
                $new_values[$new_key] = $value;
            }
            $values = $new_values;
        }

        return $values;
    }


    private function useSti()
    {
        return ($this->stiClassField && $this->stiBaseClass);
    }

    private function useMti()
    {
        return $this->mtiClassType;
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

    private function get_class_lineage($object)
    {
        $class_name = get_class($object);
        $parents = array_values(class_parents($class_name));

        return array_merge(array($class_name), $parents);
    }

    public function newFromBuilder($attributes = array(), $connection = null)
    {
        if ($this->useMti()) {
            $class = $this->class->getName();
            $instance = new $class;
            $instance->exists = true;
            $instance->setRawAttributes((array)$attributes, true);
            $parents = $this->get_class_lineage($instance);
            $query = DB::connection($this->connection);
            $base_table_set = false;
            $current_class_name = null;
            if($this->mtiClassType === 'concrete')
            {
                $current_class_name = $this->class->getShortName();
                $query = $query ->table($current_class_name);
                $base_table_set = true;
            }

            foreach ($parents as $parent) {
                if (str_contains($parent, $this->class->getShortName()) || str_contains($parent,
                        'Model') || str_contains($parent, 'BaseModelEloquent')
                ) {
                    continue;
                }

                $parent = new $parent;
                if ($parent->mtiClassType === 'abstract') {
                    continue;
                }

                $table_name = $parent->class->getShortName();

                if($base_table_set === true)
                    $query->leftJoin($table_name, $current_class_name . '.ID', '=', $table_name . '.ID');
                else
                {
                    $query = $query ->table($table_name);
                    $base_table_set = true;
                    $current_class_name = $table_name;
                }
            }
            $query->where($current_class_name . '.ID', '=', $instance->ID);
            $row = $query->first();
            $instance->setRawAttributes((array)$row, true);

            return $instance;

        } else {
            if ($this->useSti() && $attributes->{$this->stiClassField}) {
                $class = $this->class->getName();
                $instance = new $class;
                $instance->exists = true;
                $instance->setRawAttributes((array)$attributes, true);

                return $instance;
            } else {
                return parent::newFromBuilder($attributes, $connection);
            }
        }
    }
}