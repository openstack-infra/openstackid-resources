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
use App\Models\ResourceServer\Api;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Illuminate\Support\Facades\DB;

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
        $api = new Api();
        $api->setName('public-clouds');
        $api->setActive(true);
        $api->setDescription('Marketplace Public Clouds API');

        EntityManager::persist($api);

        // private clouds

        $api = new Api();
        $api->setName('private-clouds');
        $api->setActive(true);
        $api->setDescription('Marketplace Private Clouds API');

        EntityManager::persist($api);

        // consultants

        $api = new Api();
        $api->setName('consultants');
        $api->setActive(true);
        $api->setDescription('Marketplace Consultants API');

        EntityManager::persist($api);

        // summit

        $api = new Api();
        $api->setName('summits');
        $api->setActive(true);
        $api->setDescription('Summit API');

        EntityManager::persist($api);

        EntityManager::flush();

        // members

        $api = new Api();
        $api->setName('members');
        $api->setActive(true);
        $api->setDescription('Members API');

        EntityManager::persist($api);

        EntityManager::flush();

        // teams

        $api = new Api();
        $api->setName('teams');
        $api->setActive(true);
        $api->setDescription('Teams API');

        EntityManager::persist($api);

        EntityManager::flush();

    }
}