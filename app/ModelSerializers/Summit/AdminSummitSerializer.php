<?php namespace App\ModelSerializers\Summit;
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
use ModelSerializers\SummitSerializer;
/**
 * Class AdminSummitSerializer
 * @package App\ModelSerializers\Summit
 */
final class AdminSummitSerializer extends SummitSerializer
{
    protected static $array_mappings = [
        'AvailableOnApi' => 'available_on_api:json_boolean',
        'MaxSubmissionAllowedPerUser' => 'max_submission_allowed_per_user:json_int',
        'RegistrationLink' => 'registration_link:json_string',
        'Link' => 'link:json_string',
    ];
}