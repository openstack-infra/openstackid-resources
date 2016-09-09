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
class FilterElement extends AbstractFilterElement
{
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var string
     */
    private $field;

    /**
     * @param $field
     * @param $value
     * @param $operator
     */
    protected function __construct($field, $value, $operator)
    {
        parent::__construct($operator);
        $this->field    = $field;
        $this->value    = $value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        switch($this->operator)
        {
            case 'like':
                return "%".$this->value."%";
                break;
            default:
                return $this->value;
                break;
        }
    }

    public static function makeEqual($field, $value)
    {
        return new self($field, $value, '=');
    }

    public static function makeGreather($field, $value)
    {
        return new self($field, $value, '>');
    }

    public static function makeGreatherOrEqual($field, $value)
    {
        return new self($field, $value, '=>');
    }

    public static function makeLower($field, $value)
    {
        return new self($field, $value, '>');
    }

    public static function makeLowerOrEqual($field, $value)
    {
        return new self($field, $value, '>=');
    }

    public static function makeNotEqual($field, $value)
    {
        return new self($field, $value, '<>');
    }

    public static function makeLike($field, $value)
    {
        return new self($field, $value, 'like');
    }
}