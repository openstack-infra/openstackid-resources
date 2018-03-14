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
use App\Models\Foundation\Summit\Locations\Banners\ScheduledSummitLocationBanner;
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner;
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBannerConstants;
use models\exceptions\ValidationException;
/**
 * Class SummitLocationBannerValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitLocationBannerValidationRulesFactory
{
    /**
     * @param array $data
     * @param bool $update
     * @return array
     * @throws ValidationException
     */
    public static function build(array $data, $update = false)
    {
        if(!isset($data['class_name']))
            throw new ValidationException('class_name is not set');

        $base_rules = [
            'class_name' => sprintf('required|in:%s',  implode(",", SummitLocationBannerConstants::$valid_class_names)),
            'title'      => 'required|string',
            'content'    => 'required|string',
            'type'       => sprintf('required|in:%s', implode(",", SummitLocationBannerConstants::$valid_types)),
            'enabled'    => 'required|boolean'
        ];

        if($update){
            $base_rules = [
                'class_name' => sprintf('required|in:%s',  implode(",", SummitLocationBannerConstants::$valid_class_names)),
                'title'      => 'sometimes|string',
                'content'    => 'sometimes|string',
                'type'       => sprintf('sometimes|in:%s', implode(",", SummitLocationBannerConstants::$valid_types)),
                'enabled'    => 'sometimes|boolean'
            ];
        }

        switch($data['class_name']){
            case SummitLocationBanner::ClassName: {
                return $base_rules;
            }
            break;
            case ScheduledSummitLocationBanner::ClassName: {
                $extended_rules = [
                    'start_date'  => 'required|date_format:U',
                    'end_date'    => 'required_with:start_date|date_format:U|after:start_date',
                ];

                if($update){
                    $extended_rules = [
                    'start_date'  => 'sometimes|date_format:U',
                    'end_date'    => 'required_with:start_date|date_format:U|after:start_date',
                    ];
                }

                return array_merge($base_rules, $extended_rules);
            }
            default:
                throw new ValidationException("invalid class_name");
            break;
        }
        return [];
    }
}