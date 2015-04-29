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
use models\resource_server\ApiScope;
use Illuminate\Support\Facades\Config;

/**
 * Class ApiScopesSeeder
 */
class ApiScopesSeeder extends Seeder {

    public function run()
    {
        DB::table('endpoint_api_scopes')->delete();
        DB::table('api_scopes')->delete();

        $this->seedPublicCloudScopes();
        $this->seedPrivateCloudScopes();
        $this->seedConsultantScopes();
    }

    private function seedPublicCloudScopes(){

        $current_realm  = Config::get('app.url');
        $public_clouds  = Api::where('name','=','public-clouds')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/public-clouds/read',$current_realm),
                'short_description'  => 'Get Public Clouds',
                'description'        => 'Grants read only access for Public Clouds',
                'api_id'             => $public_clouds->id,
                'system'             => false,
            )
        );
    }

    private function seedPrivateCloudScopes(){

        $current_realm  = Config::get('app.url');
        $private_clouds = Api::where('name','=','private-clouds')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/private-clouds/read',$current_realm),
                'short_description'  => 'Get Private Clouds',
                'description'        => 'Grants read only access for Private Clouds',
                'api_id'             => $private_clouds->id,
                'system'             => false,
            )
        );
    }

    private function seedConsultantScopes(){

        $current_realm  = Config::get('app.url');
        $consultants = Api::where('name','=','consultants')->first();

        ApiScope::create(
            array(
                'name'               => sprintf('%s/consultants/read',$current_realm),
                'short_description'  => 'Get Consultants',
                'description'        => 'Grants read only access for Consultants',
                'api_id'             => $consultants->id,
                'system'             => false,
            )
        );
    }


}