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

return array(
	/**
	* http://www.w3.org/TR/cors/#access-control-allow-headers-response-header
	 */
	'allowed_headers'        => env('CORS_ALLOWED_HEADERS', 'origin, content-type, accept, authorization, x-requested-with'),
	/**
	* http://www.w3.org/TR/cors/#access-control-allow-methods-response-header
	*/
	'allowed_methods'        => env('CORS_ALLOWED_METHODS', 'GET, POST, OPTIONS, PUT, DELETE'),
	'use_pre_flight_caching' => env('CORS_USE_PRE_FLIGHT_CACHING', true),
	/**
	* http://www.w3.org/TR/cors/#access-control-max-age-response-header
	*/
	'max_age'                => env('CORS_MAX_AGE', 3200),
	'exposed_headers'        => env('CORS_EXPOSED_HEADERS', ''),
);