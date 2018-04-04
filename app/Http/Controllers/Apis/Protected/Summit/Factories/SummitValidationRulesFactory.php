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
 * Class SummitValidationRulesFactory
 * @package App\Http\Controllers
 */
final class SummitValidationRulesFactory
{
    public static function build(array $data, $update = false){

        if($update){
            return [
                'name'                      => 'sometimes|string|max:50',
                'start_date'                => 'sometimes|date_format:U',
                'end_date'                  => 'required_with:start_date|date_format:U|after:start_date',
                'submission_begin_date'     => 'sometimes|date_format:U',
                'submission_end_date'       => 'required_with:submission_begin_date|date_format:U|after:submission_begin_date',
                'voting_begin_date'         => 'sometimes|date_format:U',
                'voting_end_date'           => 'required_with:voting_begin_date|date_format:U|after:voting_begin_date',
                'selection_begin_date'      => 'sometimes|date_format:U',
                'selection_end_date'        => 'required_with:selection_begin_date|date_format:U|after:selection_begin_date',
                'registration_begin_date'   => 'sometimes|date_format:U',
                'registration_end_date'     => 'required_with:registration_begin_date|date_format:U|after:registration_begin_date',
                'start_showing_venues_date' => 'sometimes|date_format:U|before:start_date',
                'schedule_start_date'       => 'sometimes|date_format:U',
                'active'                    => 'sometimes|boolean',
                'dates_label'               => 'sometimes|string',
                'time_zone_id'              => 'sometimes|timezone',
                'external_summit_id'        => 'sometimes|string',
                'available_on_api'          => 'sometimes|boolean',
                'calendar_sync_name'        => 'sometimes|string|max:255',
                'calendar_sync_desc'        => 'sometimes|string',
                'link'                      => 'sometimes|url',
                'registration_link'         => 'sometimes|url',
                'max_submission_allowed_per_user'  => 'sometimes|integer|min:1',
            ];
        }

        return [
            'name'                      => 'required|string|max:50',
            'start_date'                => 'required|date_format:U',
            'end_date'                  => 'required_with:start_date|date_format:U|after:start_date',
            'submission_begin_date'     => 'sometimes|date_format:U',
            'submission_end_date'       => 'required_with:submission_begin_date|date_format:U|after:submission_begin_date',
            'voting_begin_date'         => 'sometimes|date_format:U',
            'voting_end_date'           => 'required_with:voting_begin_date|date_format:U|after:voting_begin_date',
            'selection_begin_date'      => 'sometimes|date_format:U',
            'selection_end_date'        => 'required_with:selection_begin_date|date_format:U|after:selection_begin_date',
            'registration_begin_date'   => 'sometimes|date_format:U',
            'registration_end_date'     => 'required_with:registration_begin_date|date_format:U|after:registration_begin_date',
            'start_showing_venues_date' => 'sometimes|date_format:U|before:start_date',
            'schedule_start_date'       => 'sometimes|date_format:U',
            'active'                    => 'sometimes|boolean',
            'dates_label'               => 'sometimes|string',
            'time_zone_id'              => 'required|timezone',
            'external_summit_id'        => 'sometimes|string',
            'available_on_api'          => 'sometimes|boolean',
            'calendar_sync_name'        => 'sometimes|string|max:255',
            'calendar_sync_desc'        => 'sometimes|string',
            'link'                      => 'sometimes|url',
            'registration_link'         => 'sometimes|url',
            'max_submission_allowed_per_user'  => 'sometimes|integer|min:1',
        ];
    }
}