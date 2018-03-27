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
use App\Models\Foundation\Summit\Events\Presentations\PresentationCategoryGroupConstants;
use models\exceptions\ValidationException;
use models\summit\PrivatePresentationCategoryGroup;
/**
 * Class PresentationCategoryGroupValidationRulesFactory
 * @package App\Http\Controllers
 */
final class PresentationCategoryGroupValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     * @throws ValidationException
     */
    public static function build(array $data, $update = false){

        if (!isset($data['class_name']))
            throw new ValidationException("class_name parameter is mandatory");

        $class_name = trim($data['class_name']);

        if (!in_array($class_name, PresentationCategoryGroupConstants::$valid_class_names)) {
            throw new ValidationException(
                sprintf
                (
                    "class_name param has an invalid value ( valid values are %s",
                    implode(", ", PresentationCategoryGroupConstants::$valid_class_names)
                )
            );
        }

        $base_rules =  [
            'name'        => 'required|string',
            'description' => 'sometimes|string',
            'color'       => 'sometimes|hex_color',
        ];

        if($update){
            $base_rules = [
                'name'        => 'sometimes|string',
                'description' => 'sometimes|string',
                'color'       => 'sometimes|hex_color',
            ];
        }

        $specific_rules = [];

        switch ($class_name){
            case PrivatePresentationCategoryGroup::ClassName:
            {
                $specific_rules = [
                    'submission_begin_date'             => 'sometimes|date_format:U',
                    'submission_end_date'               => 'sometimes|date_format:U|required_with:submission_begin_date|after:submission_begin_date',
                    'max_submission_allowed_per_user'   => 'sometimes|integer|min:1',
                ];
            }
            break;
        }

        return array_merge($base_rules, $specific_rules);
    }
}