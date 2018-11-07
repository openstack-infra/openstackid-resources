<?php namespace App\Http\Utils;
/**
 * Copyright 2018 OpenStack Foundation
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
/**
 * Class CSVExporter
 * @package App\Http\Utils
 */
final class CSVExporter
{
    /**
     * @var CSVExporter
     */
    private static $instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return CSVExporter
     */
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new CSVExporter();
        }
        return self::$instance;
    }

    /**
     * @param array $items
     * @param string $field_separator
     * @param array $header
     * @return string
     */
    public function export(array $items, $field_separator = ",", array $header = [], array $formatters){
        $flag   = false;
        $output = '';
        foreach ($items as $row) {
            if (!$flag) {
                // display field/column names as first row
                if(!count($header))
                    $header = array_keys($row);
                array_walk($header, array($this, 'cleanData'));
                $output .= implode($field_separator, $header) . PHP_EOL;;
                $flag = true;
            }
            array_walk($row, array($this, 'cleanData'));
            $values = [];
            foreach ($header as $key){
               $val      = isset($row[$key])? $row[$key] : '';
               if(isset($formatters[$key]))
                   $val = $formatters[$key]->format($val);
               $values[] = $val;
            }
            $output .= implode($field_separator, $values) . PHP_EOL;;
        }
        return $output;
    }

    function cleanData(&$str)
    {
        if (is_null($str)) {$str = ''; return;};
        if (is_array($str)) {$str = ''; return;};
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        $str = preg_replace("/,/", "-", $str);
        if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    }
}