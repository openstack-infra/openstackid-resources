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
                'name' => 'get-summits',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits',
                'http_method' => 'GET'
            )
        );

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
                'name' => 'get-published-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/published',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-all-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/events',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-all-published-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/events/published',
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
                'name' => 'get-published-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}/published',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'add-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'update-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'publish-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'unpublish-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'DELETE'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'delete-event',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'DELETE'
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
                'name' => 'add-event-feedback-v2',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v2/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-event-feedback',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback/{attendee_id?}',
                'http_method' => 'GET'
            )
        );

        // locations

        ApiEndpoint::create(
            array(
                'name' => 'get-locations',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-venues',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/venues',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-external-locations',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/external-locations',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-hotels',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/hotels',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-airports',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/airports',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-location',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/{location_id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-location-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/{location_id}/events',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'get-location-published-events',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/locations/{location_id}/events/published',
                'http_method' => 'GET'
            )
        );

        // event types

        ApiEndpoint::create(
            array(
                'name' => 'get-event-types',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/event-types',
                'http_method' => 'GET'
            )
        );

        //summit types

        ApiEndpoint::create(
            array(
                'name' => 'get-summit-types',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/summit-types',
                'http_method' => 'GET'
            )
        );

        //external orders

        ApiEndpoint::create(
            array(
                'name' => 'get-external-order',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/external-orders/{external_order_id}',
                'http_method' => 'GET'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'confirm-external-order',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/external-orders/{external_order_id}/external-attendees/{external_attendee_id}/confirm',
                'http_method' => 'POST'
            )
        );

        //videos


        ApiEndpoint::create(
            array(
                'name' => 'create-presentation-video',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos',
                'http_method' => 'POST'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'update-presentation-video',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'PUT'
            )
        );

        ApiEndpoint::create(
            array(
                'name' => 'delete-presentation-video',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'DELETE'
            )
        );

        //members

        ApiEndpoint::create(
            array(
                'name' => 'get-own-member',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/members/me',
                'http_method' => 'GET'
            )
        );

        // notifications

        ApiEndpoint::create(
            array(
                'name' => 'get-notifications',
                'active' => true,
                'api_id' => $summit->id,
                'route' => '/api/v1/summits/{id}/notifications',
                'http_method' => 'GET'
            )
        );

        $member_read_scope             = ApiScope::where('name', '=', sprintf('%s/me/read', $current_realm))->first();
        $summit_read_scope             = ApiScope::where('name', '=', sprintf('%s/summits/read', $current_realm))->first();
        $summit_write_scope            = ApiScope::where('name', '=', sprintf('%s/summits/write', $current_realm))->first();
        $summit_write_event_scope      = ApiScope::where('name', '=', sprintf('%s/summits/write-event', $current_realm))->first();
        $summit_publish_event_scope    = ApiScope::where('name', '=', sprintf('%s/summits/publish-event', $current_realm))->first();
        $summit_delete_event_scope     = ApiScope::where('name', '=', sprintf('%s/summits/delete-event', $current_realm))->first();
        $summit_external_order_read    = ApiScope::where('name', '=', sprintf('%s/summits/read-external-orders', $current_realm))->first();
        $summit_external_order_confirm = ApiScope::where('name', '=', sprintf('%s/summits/confirm-external-orders', $current_realm))->first();
        $write_videos_scope            = ApiScope::where('name', '=', sprintf('%s/summits/write-videos', $current_realm))->first();
        $read_notifications            = ApiScope::where('name', '=', sprintf('%s/summits/read-notifications', $current_realm))->first();

        // read
        $endpoint = ApiEndpoint::where('name', '=', 'get-summits')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-own-member')->first();
        $endpoint->scopes()->attach($member_read_scope->id);
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
        $endpoint = ApiEndpoint::where('name', '=', 'get-published-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-all-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-all-published-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-event')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-published-event')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-event-feedback')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-locations')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-venues')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-hotels')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-airports')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-external-locations')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-location')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-event-types')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-summit-types')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-location-published-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'get-location-events')->first();
        $endpoint->scopes()->attach($summit_read_scope->id);

        // read external orders

        $endpoint = ApiEndpoint::where('name', '=', 'get-external-order')->first();
        $endpoint->scopes()->attach($summit_external_order_read->id);

        // read notifications

        $endpoint = ApiEndpoint::where('name', '=', 'get-notifications')->first();
        $endpoint->scopes()->attach($read_notifications->id);

        // write

        $endpoint = ApiEndpoint::where('name', '=', 'delete-event-attendee-schedule')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'checking-event-attendee-schedule')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'add-speaker-feedback')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'add-event-feedback')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);

        $endpoint = ApiEndpoint::where('name', '=', 'add-event-feedback-v2')->first();
        $endpoint->scopes()->attach($summit_write_scope->id);

        // write events
        $endpoint = ApiEndpoint::where('name', '=', 'add-event')->first();
        $endpoint->scopes()->attach($summit_write_event_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'update-event')->first();
        $endpoint->scopes()->attach($summit_write_event_scope->id);


        $endpoint = ApiEndpoint::where('name', '=', 'publish-event')->first();
        $endpoint->scopes()->attach($summit_publish_event_scope->id);
        $endpoint = ApiEndpoint::where('name', '=', 'unpublish-event')->first();
        $endpoint->scopes()->attach($summit_publish_event_scope->id);

        $endpoint = ApiEndpoint::where('name', '=', 'delete-event')->first();
        $endpoint->scopes()->attach($summit_delete_event_scope->id);

        //confirm external order

        $endpoint = ApiEndpoint::where('name', '=', 'confirm-external-order')->first();
        $endpoint->scopes()->attach($summit_external_order_confirm->id);

        //write videos

        $endpoint = ApiEndpoint::where('name', '=', 'create-presentation-video')->first();
        $endpoint->scopes()->attach($write_videos_scope->id);

        $endpoint = ApiEndpoint::where('name', '=', 'update-presentation-video')->first();
        $endpoint->scopes()->attach($write_videos_scope->id);

        $endpoint = ApiEndpoint::where('name', '=', 'delete-presentation-video')->first();
        $endpoint->scopes()->attach($write_videos_scope->id);


    }

}