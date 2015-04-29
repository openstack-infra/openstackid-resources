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

use Illuminate\Support\Facades\App;

class RequestUtils {

	public static function getCurrentRoutePath($request)
	{
		try
		{
			//gets routes from container and try to find the route
			$router = App::make('router');
			$routes = $router->getRoutes();
			$route  = $routes->match($request);
			if (!is_null($route))
			{
				$route = $route->getPath();
				if (strpos($route, '/') != 0)
				{
					$route = '/' . $route;
				}
				return $route;
			}
		}
		catch (\Exception $ex)
		{
			Log::error($ex);
		}
		return false;
	}

}