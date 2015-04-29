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
use Illuminate\Database\Seeder;
use models\resource_server\Api;
/**
* Class ApisTableSeeder
*/
final class ApiSeeder extends Seeder
{

	public function run()
	{

		DB::table('endpoint_api_scopes')->delete();
		DB::table('api_scopes')->delete();
		DB::table('api_endpoints')->delete();
		DB::table('apis')->delete();
		// public clouds
		Api::create(
						array(
							'name'            => 'public-clouds',
							'active'          =>  true,
							'Description'     => 'Marketplace Public Clouds'
						)
		);
		// private clouds
		Api::create(
						array(
							'name'            => 'private-clouds',
							'active'          =>  true,
							'Description'     => 'Marketplace Private Clouds'
						)
		);
		// consultants
		Api::create(
						array(
							'name'            => 'consultants',
							'active'          =>  true,
							'Description'     => 'Marketplace Consultants'
						)
		);
	}
}