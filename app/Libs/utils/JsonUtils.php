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

namespace libs\utils;

/**
 * Class JsonUtils
 * http://json.org/
 * @package libs\utils
 */
abstract class JsonUtils
{
    /**
     * A string is a sequence of zero or more Unicode characters, wrapped in double quotes, using backslash escapes.
     * A character is represented as a single character string. A string is very much like a C or Java string.
     * @param string $value
     * @return string
     */
    public static function toJsonString($value)
    {
        if(empty($value)) return $value;
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    public static function toJsonBoolean($value)
    {
        if(empty($value)) return false;
        return boolval($value);
    }
}