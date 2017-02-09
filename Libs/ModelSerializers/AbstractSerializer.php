<?php namespace Libs\ModelSerializers;

/**
 * Copyright 2016 OpenStack Foundation
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

use libs\utils\JsonUtils;
use models\utils\IEntity;

/**
 * Class AbstractSerializer
 * @package Libs\ModelSerializers
 */
abstract class AbstractSerializer implements IModelSerializer
{
    /**
     * @var IEntity
     */
    protected $object;

    /**
     * AbstractSerializer constructor.
     * @param $object
     */
    public function __construct($object){
        $this->object = $object;

    }

    protected static $array_mappings    = array();

    protected static $allowed_fields    = array();

    protected static $allowed_relations = array();

    /**
     * @return array
     */
    protected function getAllowedFields()
    {
        $mappings  = array();
        $hierarchy = $this->getClassHierarchy();

        foreach($hierarchy as $class_name){
            if($class_name === 'Libs\ModelSerializers\AbstractSerializer') continue;
            $class    = new $class_name($this->object);
            $mappings = array_merge($mappings, $class->getSelfAllowedFields());
        }
        $mappings  = array_merge($mappings, $this->getSelfAllowedFields());
        return $mappings;
    }

    private function getSelfAllowedFields(){
        return static::$allowed_fields;
    }

    /**
     * @return array
     */
    protected function getAllowedRelations()
    {
        $mappings  = array();
        $hierarchy = $this->getClassHierarchy();

        foreach($hierarchy as $class_name){
            if($class_name === 'Libs\ModelSerializers\AbstractSerializer') continue;
            $class    = new $class_name($this->object);
            $mappings = array_merge($mappings, $class->getSelfAllowedRelations());
        }
        $mappings  = array_merge($mappings, $this->getSelfAllowedRelations());
        return $mappings;
    }

    private function getSelfAllowedRelations(){
        return static::$allowed_relations;
    }

    /**
     * @return array
     */
    private function getAttributeMappings()
    {
        $mappings  = array();
        $hierarchy = $this->getClassHierarchy();

        foreach($hierarchy as $class_name){
            if($class_name === 'Libs\ModelSerializers\AbstractSerializer') continue;
            $class    = new $class_name($this->object);
            $mappings = array_merge($mappings, $class->getSelfMappings());
        }
        $mappings  = array_merge($mappings, $this->getSelfMappings());
        return $mappings;
    }

    private function getSelfMappings(){
        return static::$array_mappings;
    }

    /**
     * @return array
     */
    private function getClassHierarchy(){
        return array_reverse($this->get_class_lineage($this));
    }

    private function get_class_lineage($object)
    {
        $class_name = get_class($object);
        $parents = array_values(class_parents($class_name));
        return array_merge(array($class_name), $parents);
    }

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $values   = array();
        if(!count($fields)) $fields       = $this->getAllowedFields();
        $mappings                         = $this->getAttributeMappings();

        if (count($mappings)) {
            $new_values = array();
            foreach ($mappings as $attribute => $mapping) {
                $mapping = preg_split('/:/',$mapping);
                if(count($fields) > 0 && !in_array($mapping[0], $fields)) continue;
                $value   = call_user_func( array( $this->object, 'get'.$attribute ) );
                if(count($mapping) > 1)
                {
                    //we have a formatter ...
                    switch(strtolower($mapping[1]))
                    {
                        case 'datetime_epoch':
                        {
                            if(!is_null($value)) {
                                 $value = $value->getTimestamp();
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
                $new_values[$mapping[0]] = $value;
            }
            $values = $new_values;
        }

        return $values;
    }

    /**
     * @param string $expand_str
     * @param string $prefix
     * @return string
     */
    protected static function filterExpandByPrefix($expand_str, $prefix ){

        $expand_to    = explode(',', $expand_str);
        $filtered_expand  = array_filter($expand_to, function($element) use($prefix){
            return preg_match('/^' . preg_quote($prefix, '/') . '/', strtolower(trim($element))) > 0;
        });
        $res = '';
        foreach($filtered_expand as $filtered_expand_elem){
            if(strlen($res) > 0) $res .= ',';
            $res .= explode('.', strtolower(trim($filtered_expand_elem)))[1];
        }

        return $res;
    }
}