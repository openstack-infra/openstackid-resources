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

final class SummitPushNotificationValidationRulesFactory
{
    /**
     * @param array $data
     * @return array
     */
    public static function build(array $data){
        return [
            'message'       => 'required|string',
            'platform'      => 'required|in:MOBILE,WEB',
            'channel'       => 'required_if:platform,MOBILE|in:EVERYONE,SPEAKERS,ATTENDEES,MEMBERS,SUMMIT,EVENT,GROUP',
            'event_id'      => 'required_if:channel,EVENT|integer',
            'group_id'      => 'required_if:channel,GROUP|integer',
            'recipient_ids' => 'required_if:channel,MEMBERS|int_array',
        ];
    }
}