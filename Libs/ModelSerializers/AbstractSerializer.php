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

/**
 * Class AbstractSerializer
 * @package Libs\ModelSerializers
 */
abstract class AbstractSerializer implements IModelSerializer
{
    /**
     * @var
     */
    protected $object;

    /**
     * AbstractSerializer constructor.
     * @param $object
     */
    public function __construct($object){
        $this->object = $object;

    }

    protected static $array_mappings = array();

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
     * @param string $expand
     * @return array
     */
    public function serialize($expand = null)
    {
        $values   = array();

        $mappings = $this->getAttributeMappings();
        if (count($mappings)) {
            $new_values = array();
            foreach ($mappings as $attribute => $mapping) {
                $mapping = preg_split('/:/',$mapping);
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
}