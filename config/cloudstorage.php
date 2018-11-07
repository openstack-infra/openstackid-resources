<?php
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

return [
    'base_url' => env('CLOUD_STORAGE_BASE_URL', null),
    'container' => env('CLOUD_STORAGE_CONTAINER', null),
    'auth_url' => env('CLOUD_STORAGE_AUTH_URL', null),
    'user_name' => env('CLOUD_STORAGE_USERNAME', null),
    'api_key' => env('CLOUD_STORAGE_APIKEY', null),
    'project_name' => env('CLOUD_STORAGE_PROJECT_NAME', null),
    'region' => env('CLOUD_STORAGE_REGION', null),
];