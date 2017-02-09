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
use App\Models\ResourceServer\ApiEndpoint;
use LaravelDoctrine\ORM\Facades\EntityManager;

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
        $this->seedMemberEndpoints();
        $this->seedTeamEndpoints();
    }

    /**
     * @param string $api_name
     * @param array $endpoints_info
     */
    private function seedApiEndpoints($api_name, array $endpoints_info){

        $api = EntityManager::getRepository(\App\Models\ResourceServer\Api::class)->findOneBy(['name' => $api_name]);
        if(is_null($api)) return;

        foreach($endpoints_info as $endpoint_info){

            $endpoint = new ApiEndpoint();
            $endpoint->setName($endpoint_info['name']);
            $endpoint->setRoute($endpoint_info['route']);
            $endpoint->setHttpMethod($endpoint_info['http_method']);
            $endpoint->setActive(true);
            $endpoint->setAllowCors(true);
            $endpoint->setAllowCredentials(true);
            $endpoint->setApi($api);

            foreach($endpoint_info['scopes'] as $scope_name){
                $scope = EntityManager::getRepository(\App\Models\ResourceServer\ApiScope::class)->findOneBy(['name' => $scope_name]);
                if(is_null($scope)) continue;
                $endpoint->addScope($scope);
            }

            EntityManager::persist($endpoint);
        }

        EntityManager::flush();
    }

    private function seedPublicCloudsEndpoints()
    {

        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('public-clouds', [
            array(
                'name' => 'get-public-clouds',
                'active' => true,
                'route' => '/api/v1/marketplace/public-clouds',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf('%s/public-clouds/read', $current_realm),
                ],
            ),
            array(
                'name' => 'get-public-cloud',
                'active' => true,
                'route' => '/api/v1/marketplace/public-clouds/{id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf('%s/public-clouds/read', $current_realm),
                ],
            ),
            array(
                'name' => 'get-public-cloud-datacenters',
                'active' => true,
                'route' => '/api/v1/marketplace/public-clouds/{id}/data-centers',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf('%s/public-clouds/read', $current_realm),
                ],
            )
        ]);
    }

    private function seedPrivateCloudsEndpoints()
    {

        $current_realm = Config::get('app.url');
        // endpoints scopes

        $this->seedApiEndpoints('private-clouds', [
            array(
                'name' => 'get-private-clouds',
                'route' => '/api/v1/marketplace/private-clouds',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/private-clouds/read', $current_realm)],
            ),
            array(
                'name' => 'get-private-cloud',
                'route' => '/api/v1/marketplace/private-clouds/{id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/private-clouds/read', $current_realm)],
            ),
            array(
                'name' => 'get-private-cloud-datacenters',
                'route' => '/api/v1/marketplace/private-clouds/{id}/data-centers',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/private-clouds/read', $current_realm)],
            )
        ]);

    }

    private function seedConsultantsEndpoints()
    {

        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('consultants', [
            array(
                'name' => 'get-consultants',
                'route' => '/api/v1/marketplace/consultants',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/consultants/read', $current_realm)],
            ),
            array(
                'name' => 'get-consultant',
                'route' => '/api/v1/marketplace/consultants/{id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/consultants/read', $current_realm)],
            ),
            array(
                'name' => 'get-consultant-offices',
                'route' => '/api/v1/marketplace/consultants/{id}/offices',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/consultants/read', $current_realm)],
            )
        ]);
    }

    private function seedSummitEndpoints()
    {
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('summits', [
            // summits
            array(
                'name' => 'get-summits',
                'route' => '/api/v1/summits',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-summit',
                'route' => '/api/v1/summits/{id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-summit-entity-events',
                'route' => '/api/v1/summits/{id}/entity-events',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            // attendees
            array(
                'name' => 'get-attendees',
                'route' => '/api/v1/summits/{id}/attendees',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'add-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/write', $current_realm)],
            ),
            array(
                'name' => 'delete-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/summits/write', $current_realm)],
            ),
            array(
                'name' => 'checking-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}/check-in',
                'http_method' => 'PUT',
                'scopes' => [sprintf('%s/summits/write', $current_realm)],
            ),
            // speakers
            array(
                'name' => 'get-speakers',
                'route' => '/api/v1/summits/{id}/speakers',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-speaker',
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'add-speaker-feedback',
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}/presentations/{presentation_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/write', $current_realm)],
            ),
            // events
            array(
                'name' => 'get-events',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-published-events',
                'route' => '/api/v1/summits/{id}/events/published',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-all-events',
                'route' => '/api/v1/summits/events',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-all-published-events',
                'route' => '/api/v1/summits/events/published',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-published-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/published',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'add-event',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/write-event', $current_realm)],
            ),
            array(
                'name' => 'update-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'PUT',
                'scopes' => [sprintf('%s/summits/write-event', $current_realm)],
            ),
            array(
                'name' => 'publish-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'PUT',
                'scopes' => [sprintf('%s/summits/publish-event', $current_realm)],
            ),
            array(
                'name' => 'unpublish-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/summits/publish-event', $current_realm)],
            ),
            array(
                'name' => 'delete-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/summits/delete-event', $current_realm)],
            ),
            array(
                'name' => 'add-event-feedback',
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/write', $current_realm)],
            ),
            array(
                'name' => 'add-event-feedback-v2',
                'route' => '/api/v2/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/write', $current_realm)],
            ),
            array(
                'name' => 'get-event-feedback',
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback/{attendee_id?}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            // locations
             array(
                 'name' => 'get-locations',
                 'route' => '/api/v1/summits/{id}/locations',
                 'http_method' => 'GET',
                 'scopes' => [sprintf('%s/summits/read', $current_realm)],
             ),
            array(
                'name' => 'get-venues',
                'route' => '/api/v1/summits/{id}/locations/venues',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-external-locations',
                'route' => '/api/v1/summits/{id}/locations/external-locations',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-hotels',
                'route' => '/api/v1/summits/{id}/locations/hotels',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-airports',
                'route' => '/api/v1/summits/{id}/locations/airports',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-location',
                'route' => '/api/v1/summits/{id}/locations/{location_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-location-events',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/events',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-location-published-events',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/events/published',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            // event types
            array(
                'name' => 'get-event-types',
                'route' => '/api/v1/summits/{id}/event-types',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            //tracks
            array(
                'name' => 'get-tracks',
                'route' => '/api/v1/summits/{id}/tracks',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-track',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-track-groups',
                'route' => '/api/v1/summits/{id}/track-groups',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            //external orders
            array(
                'name' => 'get-external-order',
                'route' => '/api/v1/summits/{id}/external-orders/{external_order_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read-external-orders', $current_realm)],
            ),
            array(
                'name' => 'confirm-external-order',
                'route' => '/api/v1/summits/{id}/external-orders/{external_order_id}/external-attendees/{external_attendee_id}/confirm',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/confirm-external-orders', $current_realm)],
            ),
            //videos
            array(
                'name' => 'get-presentation-videos',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'get-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/video/{video_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read', $current_realm)],
            ),
            array(
                'name' => 'create-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/summits/write-videos', $current_realm)],
            ),
            array(
                'name' => 'update-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'PUT',
                'scopes' => [sprintf('%s/summits/write-videos', $current_realm)],
            ),
            array(
                'name' => 'delete-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/summits/write-videos', $current_realm)],
            ),
            //members
            array(
                'name' => 'get-own-member',
                'route' => '/api/v1/summits/{id}/members/{member_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/me/read', $current_realm)],
            ),
            array(
                'name' => 'get-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/me/read', $current_realm)],
            ),
            array(
                'name' => 'add-2-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites/{event_id}',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/me/summits/events/favorites/add', $current_realm)],
            ),
            array(
                'name' => 'remove-from-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/me/summits/events/favorites/delete', $current_realm)],
            ),
            // notifications
            array(
                'name' => 'get-notifications',
                'route' => '/api/v1/summits/{id}/notifications',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/summits/read-notifications', $current_realm)],
            )
        ]);

    }

    private function seedMemberEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('members', [
               // members
                array(
                    'name' => 'get-members',
                    'route' => '/api/v1/members',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/members/read', $current_realm)],
                )
            ]
        );
    }

    private function seedTeamEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('teams', [
                array(
                    'name' => 'add-team',
                    'route' => '/api/v1/teams',
                    'http_method' => 'POST',
                    'scopes' => [sprintf('%s/teams/write', $current_realm)],
                ),
                array(
                    'name' => 'update-team',
                    'route' => '/api/v1/teams/{team_id}',
                    'http_method' => 'PUT',
                    'scopes' => [sprintf('%s/teams/write', $current_realm)],
                ),
                array(
                    'name' => 'delete-team',
                    'route' => '/api/v1/teams/{team_id}',
                    'http_method' => 'DELETE',
                    'scopes' => [sprintf('%s/teams/write', $current_realm)],
                ),
                array(
                    'name' => 'get-teams',
                    'route' => '/api/v1/teams',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/teams/read', $current_realm)],
                ),
                array(
                    'name' => 'get-team',
                    'route' => '/api/v1/teams/{team_id}',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/teams/read', $current_realm)],
                ),
                array(
                    'name' => 'post-message-2-team',
                    'route' => '/api/v1/teams/{team_id}/messages',
                    'http_method' => 'POST',
                    'scopes' => [sprintf('%s/teams/write', $current_realm)],
                ),
                array(
                    'name' => 'get-messages-from-team',
                    'route' => '/api/v1/teams/{team_id}/messages',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/teams/read', $current_realm)],
                ),

                array(
                    'name' => 'add-member-2-team',
                    'route' => '/api/v1/teams/{team_id}/members/{member_id}',
                    'http_method' => 'POST',
                    'scopes' => [sprintf('%s/teams/write', $current_realm)],
                ),
                array(
                    'name' => 'remove-member-from-team',
                    'route' => '/api/v1/teams/{team_id}/members/{member_id}',
                    'http_method' => 'DELETE',
                    'scopes' => [sprintf('%s/teams/write', $current_realm)],
                ),
            ]
        );

        $this->seedApiEndpoints('members', [
                array(
                    'name' => 'get-invitations',
                    'route' => '/api/v1/members/me/team-invitations',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/members/invitations/read', $current_realm)],
                ),
                array(
                    'name'        => 'get-pending-invitations',
                    'route'       => '/api/v1/members/me/team-invitations/pending',
                    'http_method' => 'GET',
                    'scopes'      => [sprintf('%s/members/invitations/read', $current_realm)],
                ),
                array(
                    'name' => 'get-accepted-invitations',
                    'route' => '/api/v1/members/me/team-invitations/accepted',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/members/invitations/read', $current_realm)],
                ),
                array(
                    'name' => 'accept-invitation',
                    'route' => '/api/v1/members/me/team-invitations/{invitation_id}',
                    'http_method' => 'PUT',
                    'scopes' => [sprintf('%s/members/invitations/write', $current_realm)],
                ),
                array(
                    'name' => 'decline-invitation',
                    'route' => '/api/v1/members/me/team-invitations/{invitation_id}',
                    'http_method' => 'DELETE',
                    'scopes' => [sprintf('%s/members/invitations/write', $current_realm)],
                ),
            ]
        );
    }

}