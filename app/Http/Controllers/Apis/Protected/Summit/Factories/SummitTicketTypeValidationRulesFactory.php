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
 * Class SummitTicketTypeValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitTicketTypeValidationRulesFactory
{
    /**
     * @param array $data
     * @return array
     */
    public static function build(array $data, $update = false){
        if($update){
            return [
                'name'        => 'sometimes|string',
                'description' => 'sometimes|string',
                'external_id' => 'sometimes|string|max:255',
            ];
        }

        return [
            'name'        => 'required|string',
            'description' => 'sometimes|string',
            'external_id' => 'required|string|max:255',
        ];
    }


}