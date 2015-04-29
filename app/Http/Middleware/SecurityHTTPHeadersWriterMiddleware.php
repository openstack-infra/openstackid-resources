<?php namespace App\Http\Middleware;
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

use Closure;
use Illuminate\Contracts\Routing\Middleware;

/**
* Class SecurityHTTPHeadersWriterMiddleware
* https://www.owasp.org/index.php/List_of_useful_HTTP_headers
*
* @package App\Http\Middleware
*/
class SecurityHTTPHeadersWriterMiddleware implements Middleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return \Illuminate\Http\Response
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		// https://www.owasp.org/index.php/List_of_useful_HTTP_headers
		$response->headers->set('X-content-type-options', 'nosniff');
		$response->headers->set('X-xss-protection', '1; mode=block');
		// http://tools.ietf.org/html/rfc6797
		/**
		 * The HSTS header field below stipulates that the HSTS Policy is to
		 * remain in effect for one year (there are approximately 31536000
		 * seconds in a year)
		 * applies to the domain of the issuing HSTS Host and all of its
		 * subdomains:
		 */
		$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
		return $response;
	}
}