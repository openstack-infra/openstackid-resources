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
use libs\utils\JsonUtils;
use ReflectionClass;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
/**
 * Class BaseModelEloquent
 */
class BaseModelEloquent extends Eloquent
{

    private $class = null;

    protected static $array_mappings = array();

    /**
     * Register a restoring model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     * @return void
     */
    public static function restoring($callback, $priority = 0)
    {
        static::registerModelEvent('restoring', $callback, $priority);
    }
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

    /**
     * @return array
     */
    public function getAttributeMappings(){
        $mappings  = array();

        $hierarchy = $this->getClassHierarchy();
        foreach($hierarchy as $class_name){
            if($class_name == $this->class->getName()) continue;
            $class    = new $class_name;
            if($class instanceof BaseModelEloquent)
                $mappings = array_merge($mappings, $class->getSelfMappings());
        }
        $mappings  = array_merge($mappings, $this->getSelfMappings());
        return $mappings;
    }

    public function getSelfMappings(){
        return static::$array_mappings;
    }

    /**
     * @return array
     */
    public function getClassHierarchy(){
        $class_hierarchy = array();

        if ($this->useMti()) {
            $class = $this->class->getName();
            $parents = $this->get_class_lineage(new $class);

            if ($this->mtiClassType === 'concrete') {
                $base_class_name = $this->class->getName();
                array_push($class_hierarchy, $base_class_name);
            }

            foreach ($parents as $parent) {

                if (!$this->isAllowedParent($parent)) {
                    continue;
                }

                $parent = new $parent;
                if ($parent->mtiClassType === 'abstract') {
                    continue;
                }

                array_push($class_hierarchy, $parent->class->getName());
            }
        }
        return array_reverse($class_hierarchy);
    }

    public function toArray()
    {
        $values   = parent::toArray();
        $mappings = $this->getAttributeMappings();
        if (count($mappings)) {
            $new_values = array();
            foreach ($mappings as $old_key => $new_key) {
                $value = isset($values[$old_key])? $values[$old_key] :
                    (
                        isset($values['pivot'])? (
                            isset($values['pivot'][$old_key]) ? $values['pivot'][$old_key] : null
                        ): null
                    );

                $new_key = preg_split('/:/',$new_key);
                if(count($new_key) > 1)
                {
                    //we have a formatter ...
                    switch(strtolower($new_key[1]))
                    {
                        case 'datetime_epoch':
                        {
                            if(!is_null($value)) {
                                $datetime = new \DateTime($value);
                                $value = $datetime->getTimestamp();
                            }
                        }
                        break;
                        case 'json_string':
                        {
                            $value = JsonUtils::toJsonString($value);
                        }
                        break;
                        case 'json_boolean':
                        {
                            $value = JsonUtils::toJsonBoolean($value);
                        }
                        break;
                        case 'json_int':
                        {
                            $value = JsonUtils::toJsonInt($value);
                        }
                        break;
                        case 'json_float':
                        {
                            $value = JsonUtils::toJsonFloat($value);
                        }
                        break;
                    }
                }
                $new_values[$new_key[0]] = $value;
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
        if ($this->useMti()) {
            $query = $builder->getQuery();
            $class = $this->class->getName();
            $parents = $this->get_class_lineage(new $class);
            $base_table_set = false;
            $current_class_name = null;
            if ($this->mtiClassType === 'concrete') {
                $current_class_name = $this->class->getShortName();
                $query = $query->from($current_class_name);
                $base_table_set = true;
            }

            foreach ($parents as $parent) {

                if(!$this->isAllowedParent($parent))
                {
                    continue;
                }

                $parent = new $parent;
                if ($parent->mtiClassType === 'abstract') {
                    continue;
                }

                $table_name = $parent->class->getShortName();

                if ($base_table_set === true) {
                    $query->leftJoin($table_name, $current_class_name . '.ID', '=', $table_name . '.ID');
                } else {
                    $query = $query->from($table_name);
                    $base_table_set = true;
                    $current_class_name = $table_name;
                }
            }

        } else {
            if ($this->useSti() && get_class(new $this->stiBaseClass) !== get_class($this)) {
                $builder->where($this->stiClassField, "=", $this->class->getShortName());
            }
        }

        return $builder;
    }

    protected function isAllowedParent($parent_name)
    {
        $res = str_contains($parent_name, $this->class->getShortName()) ||
               str_contains($parent_name,'Illuminate\Database\Eloquent\Model') ||
               str_contains($parent_name, 'models\utils\BaseModelEloquent');
        return !$res;
    }

    private function get_class_lineage($object)
    {
        $class_name = get_class($object);
        $parents = array_values(class_parents($class_name));

        return array_merge(array($class_name), $parents);
    }

    public function newFromBuilder($attributes = array(), $connection = null)
    {
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

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @param  bool  $prefix_fkey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null, $prefix_fkey = true)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $instance   = new $related;
        $table_name = $instance->getTable();

        $localKey = $localKey ?: $this->getKeyName();
        if($prefix_fkey) $foreignKey = $table_name . '.' . $foreignKey;

        return new HasMany($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $query           = $this->newQueryWithoutScopes();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false)
        {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists)
        {
            $saved = $this->performUpdate($query, $options);
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else
        {
            $saved = $this->performInsert($query, $options);
        }

        if ($saved) $this->finishSave($options);

        return $saved;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = [])
    {
        if ($this->fireModelEvent('creating') === false) return false;

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->timestamps && array_get($options, 'timestamps', true))
        {
            $this->updateTimestamps();
        }

        $class_hierarchy = array();

        if ($this->useMti())
        {
            $class = $this->class->getName();
            $parents = $this->get_class_lineage(new $class);

            if ($this->mtiClassType === 'concrete')
            {
                $base_class_name = $this->class->getShortName();
                array_push($class_hierarchy, $base_class_name);
            }

            foreach ($parents as $parent) {

                if(!$this->isAllowedParent($parent))
                {
                    continue;
                }

                $parent = new $parent;
                if ($parent->mtiClassType === 'abstract') {
                    continue;
                }

                array_push($class_hierarchy, $parent->class->getShortName());
            }
            $attributes = $this->attributes;
            do{
                $table = array_pop($class_hierarchy);
                $class = new $table;

            }while(true);
        }
        else {
            // If the model has an incrementing key, we can use the "insertGetId" method on
            // the query builder, which will give us back the final inserted ID for this
            // table from the database. Not all tables have to be incrementing though.
            $attributes = $this->attributes;

            if ($this->incrementing) {
                $this->insertAndSetId($query, $attributes);
            }

            // If the table is not incrementing we'll simply insert this attributes as they
            // are, as this attributes arrays must contain an "id" column already placed
            // there by the developer as the manually determined key for these models.
            else {
                $query->insert($attributes);
            }
        }
        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->fireModelEvent('created', false);

        return true;
    }
}