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

return array
(
    'ssl_enabled'                 => env('SSL_ENABLED', false),
    'oauth2_enabled'              => env('OAUTH2_ENABLED', true),
    'db_log_enabled'              => env('DB_LOG_ENABLED', false),
    'access_token_cache_lifetime' => env('ACCESS_TOKEN_CACHE_LIFETIME', 300),
    'assets_base_url'             => env('ASSETS_BASE_URL', null),
);