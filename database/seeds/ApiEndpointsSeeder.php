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
use models\resource_server\ApiEndpoint;
use models\resource_server\ApiScope;

/**
 * Class ApiEndpointsSeeder
 */
class ApiEndpointsSeeder extends Seeder
{

    public function run()
    {

        DB::table('endpoint_api_scopes')->delete();
        DB::table('api_endpoints')->delete();

        $this->seedPublicCloudsEndpoints();
        $this->seedPrivateCloudsEndpoints();
        $this->seedConsultantsEndpoints();
        $this->seedSummitEndpoints();
    }

    private function seedPublicCloudsEndpoints()
    {

        $public_clouds = Api::where('name', '=', 'public-clouds')->first();
        $current_realm = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name' => 'get-public-clouds',
                'active' => true,
                'api_id' => $public_clouds->id,
                'route' => '/api/v1/marketplace/public-clouds',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-public-cloud',
                'active' => true,
                'api_id' => $public_clouds->id,
                'route' => '/api/v1/marketplace/public-clouds/{id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-public-cloud-datacenters',
                'active' => true,
                'api_id' => $public_clouds->id,
                'route' => '/api/v1/marketplace/public-clouds/{id}/data-centers',
                'http_method' => 'GET'
            )
        );

        $public_cloud_read_scope = ApiScope::where('name', '=',
            sprintf('%s/public-clouds/read', $current_realm))->first();

        $endpoint_get_public_clouds = ApiEndpoint::where('name', '=', 'get-public-clouds')->first();
        $endpoint_get_public_clouds->scopes()->attach($public_cloud_read_scope->id);

        $endpoint_get_public_cloud = ApiEndpoint::where('name', '=', 'get-public-cloud')->first();
        $endpoint_get_public_cloud->scopes()->attach($public_cloud_read_scope->id);

        $endpoint_get_public_cloud_datacenters = ApiEndpoint::where('name', '=',
            'get-public-cloud-datacenters')->first();
        $endpoint_get_public_cloud_datacenters->scopes()->attach($public_cloud_read_scope->id);
    }

    private function seedPrivateCloudsEndpoints()
    {
        $private_clouds = Api::where('name', '=', 'private-clouds')->first();
        $current_realm = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name' => 'get-private-clouds',
                'active' => true,
                'api_id' => $private_clouds->id,
                'route' => '/api/v1/marketplace/private-clouds',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-private-cloud',
                'active' => true,
                'api_id' => $private_clouds->id,
                'route' => '/api/v1/marketplace/private-clouds/{id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-private-cloud-datacenters',
                'active' => true,
                'api_id' => $private_clouds->id,
                'route' => '/api/v1/marketplace/private-clouds/{id}/data-centers',
                'http_method' => 'GET'
            )
        );

        $private_cloud_read_scope = ApiScope::where('name', '=',
            sprintf('%s/private-clouds/read', $current_realm))->first();

        $endpoint_get_private_clouds = ApiEndpoint::where('name', '=', 'get-private-clouds')->first();
        $endpoint_get_private_clouds->scopes()->attach($private_cloud_read_scope->id);

        $endpoint_get_private_cloud = ApiEndpoint::where('name', '=', 'get-private-cloud')->first();
        $endpoint_get_private_cloud->scopes()->attach($private_cloud_read_scope->id);

        $endpoint_get_private_cloud_datacenters = ApiEndpoint::where('name', '=',
            'get-private-cloud-datacenters')->first();
        $endpoint_get_private_cloud_datacenters->scopes()->attach($private_cloud_read_scope->id);

    }

    private function seedConsultantsEndpoints()
    {

        $consultants = Api::where('name', '=', 'consultants')->first();
        $current_realm = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name' => 'get-consultants',
                'active' => true,
                'api_id' => $consultants->id,
                'route' => '/api/v1/marketplace/consultants',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-consultant',
                'active' => true,
                'api_id' => $consultants->id,
                'route' => '/api/v1/marketplace/consultants/{id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-consultant-offices',
                'active' => true,
                'api_id' => $consultants->id,
                'route' => '/api/v1/marketplace/consultants/{id}/offices',
                'http_method' => 'GET'
            )
        );

        $consultant_read_scope = ApiScope::where('name', '=', sprintf('%s/consultants/read', $current_realm))->first();

        $endpoint = ApiEndpoint::where('name', '=', 'get-consultants')->first();
        $endpoint->scopes()->attach($consultant_read_scope->id);

        $endpoint = ApiEndpoint::where('name', '=', 'get-consultant')->first();
        $endpoint->scopes()->attach($consultant_read_scope->id);

        $endpoint = ApiEndpoint::where('name', '=', 'get-consultant-offices')->first();
        $endpoint->scopes()->attach($consultant_read_scope->id);
    }

    private function seedSummitEndpoints()
    {
        $summit = Api::where('name', '=', 'summits')->first();
        $current_realm = Config::get('app.url');
        // endpoints scopes

        ApiEndpoint::create(
            array(
                'name' => 'get-summit',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-summit-entity-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/entity-events',
                'http_method' => 'GET'
            )
        );

        // attendees

        ApiEndpoint::create
        (
            array(
                'name' => 'get-attendees',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/attendees',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-attendee',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-attendee-schedule',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'add-event-attendee-schedule',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'delete-event-attendee-schedule',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'checking-event-attendee-schedule',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}/check-in',
                'http_method' => 'PUT'
            )
        );

        // speakers

        ApiEndpoint::create(
            array(
                'name' => 'get-speakers',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/speakers',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-speaker',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}',
                'http_method' => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name' => 'add-speaker-feedback',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}/presentations/{presentation_id}/feedback',
                'http_method' => 'POST'
            )
        );

        // events

        ApiEndpoint::create(
            array(
                'name' => 'get-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'GET'
            )
        );


        ApiEndpoint::create(
            array(
                'name' => 'add-event-feedback',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-event-feedback',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'GET'
            )
        );



        $summit_read_scope  = ApiScope::where('name', '=', sprintf('%s/summits/read', $current_realm))->first();
        $summit_write_scope = ApiScope::where('name', '=', sprintf('%s/summits/write', $current_realm))->first();

        // read
        $endpoint = ApiEndpoint::where('name', '=', 'get-summit')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-summit-entity-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-attendees')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-attendee')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-attendee-schedule')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'add-event-attendee-schedule')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-speakers')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-speaker')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-event')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-event-feedback')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);

        // write
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'delete-event-attendee-schedule')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'checking-event-attendee-schedule')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'add-speaker-feedback')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'add-event-feedback')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);

    }

}