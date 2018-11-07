<?php namespace libs\utils;
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

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/**
 * Class RequestUtils
 * @package libs\utils
 */
final class RequestUtils {

    /**
     * @return bool|string
     */
	public static function getCurrentRoutePath()
	{
		try
		{
            $route_path  = Route::getCurrentRoute()->uri();
            if (strpos($route_path, '/') != 0)
                $route_path = '/' . $route_path;

            return $route_path;
		}
		catch (\Exception $ex)
		{
			Log::error($ex);
		}
		return false;
	}

}