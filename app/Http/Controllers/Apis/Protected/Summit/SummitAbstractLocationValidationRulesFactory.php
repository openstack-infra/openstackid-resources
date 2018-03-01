<?php namespace App\Http\Controllers;
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
 * Class SummitAbstractLocationValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitAbstractLocationValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     */
    public static function build(array $data, $update = false){
        $name_rule = 'required|string|max:255';

        if($update){
            $name_rule = 'sometimes|string|max:255';
        }

        $rules = [
            'name'        => $name_rule,
            'description' => 'sometimes|string',
        ];

        return $rules;
    }
}