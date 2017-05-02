<?php
/**
 * Copyright 2016 OpenStack Foundation
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
    'get_summit_response_lifetime'          => env('CACHE_API_RESPONSE_GET_SUMMIT_LIFETIME', 300),
    'get_event_feedback_response_lifetime'  => env('CACHE_API_RESPONSE_GET_EVENT_FEEDBACK_LIFETIME', 300),
    'get_published_event_response_lifetime' => env('CACHE_API_RESPONSE_GET_PUBLISHED_EVENT_LIFETIME', 300),
    'get_summits_response_lifetime'         => env('CACHE_API_RESPONSE_GET_SUMMITS_LIFETIME', 600),
];