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
use Illuminate\Support\Facades\Config;
use models\resource_server\Api;
use models\resource_server\ApiScope;

/**
 * Class ApiScopesSeeder
 */
class ApiScopesSeeder extends Seeder
{

    public function run()
    {
        DB::table('endpoint_api_scopes')->delete();
        DB::table('api_scopes')->delete();

        $this->seedPublicCloudScopes();
        $this->seedPrivateCloudScopes();
        $this->seedConsultantScopes();
        $this->seedSummitScopes();
    }

    private function seedPublicCloudScopes()
    {

        $current_realm = Config::get('app.url');
        $public_clouds = Api::where('name', '=', 'public-clouds')->first();

        ApiScope::create(
            array(
                'name' => sprintf('%s/public-clouds/read', $current_realm),
                'short_description' => 'Get Public Clouds',
                'description' => 'Grants read only access for Public Clouds',
                'api_id' => $public_clouds->id,
                'system' => false
            )
        );
    }

    private function seedPrivateCloudScopes()
    {

        $current_realm = Config::get('app.url');
        $private_clouds = Api::where('name', '=', 'private-clouds')->first();

        ApiScope::create(
            array(
                'name' => sprintf('%s/private-clouds/read', $current_realm),
                'short_description' => 'Get Private Clouds',
                'description' => 'Grants read only access for Private Clouds',
                'api_id' => $private_clouds->id,
                'system' => false
            )
        );
    }

    private function seedConsultantScopes()
    {

        $current_realm = Config::get('app.url');
        $consultants = Api::where('name', '=', 'consultants')->first();

        ApiScope::create(
            array(
                'name' => sprintf('%s/consultants/read', $current_realm),
                'short_description' => 'Get Consultants',
                'description' => 'Grants read only access for Consultants',
                'api_id' => $consultants->id,
                'system' => false
            )
        );
    }

    private function seedSummitScopes()
    {

        $current_realm = Config::get('app.url');
        $summits = Api::where('name', '=', 'summits')->first();

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/read', $current_realm),
                'short_description' => 'Get Summit Data',
                'description' => 'Grants read only access for Summits Data',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/me/read', $current_realm),
                'short_description' => 'Get own member data',
                'description' => 'Grants read only access for our own member data',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/write', $current_realm),
                'short_description' => 'Write Summit Data',
                'description' => 'Grants write access for Summits Data',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/write-event', $current_realm),
                'short_description' => 'Write Summit Events',
                'description' => 'Grants write access for Summits Events',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/delete-event', $current_realm),
                'short_description' => 'Delete Summit Events',
                'description' => 'Grants delete access for Summits Events',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/publish-event', $current_realm),
                'short_description' => 'Publish/UnPublish Summit Events',
                'description' => 'Grants Publish/UnPublish access for Summits Events',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/read-external-orders', $current_realm),
                'short_description' => 'Allow to read External Orders',
                'description' => 'Allow to read External Orders',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/confirm-external-orders', $current_realm),
                'short_description' => 'Allow to confirm External Orders',
                'description' => 'Allow to confirm External Orders',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/write-videos', $current_realm),
                'short_description' => 'Allow to write presentation videos',
                'description' => 'Allow to write presentation videos',
                'api_id' => $summits->id,
                'system' => false
            )
        );

        ApiScope::create(
            array(
                'name' => sprintf('%s/summits/read-notifications', $current_realm),
                'short_description' => 'Allow to read summit notifications',
                'description' => 'Allow to read summit notifications',
                'api_id' => $summits->id,
                'system' => false
            )
        );
    }

}