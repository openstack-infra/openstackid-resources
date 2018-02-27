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
use App\Security\SummitScopes;
/**
 * Class ApiEndpointsSeeder
 */
class ApiEndpointsSeeder extends Seeder
{

    public function run()
    {
        DB::table('endpoint_api_scopes')->delete();
        DB::table('api_endpoints')->delete();

        $this->seedSummitEndpoints();
        $this->seedMemberEndpoints();
        $this->seedTeamEndpoints();
        $this->seedTagsEndpoints();
        $this->seedCompaniesEndpoints();
        $this->seedGroupsEndpoints();
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

    private function seedSummitEndpoints()
    {
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('summits', [
            // summits
            array(
                'name' => 'get-summits',
                'route' => '/api/v1/summits',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-summit',
                'route' => '/api/v1/summits/{id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-summit-entity-events',
                'route' => '/api/v1/summits/{id}/entity-events',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            // attendees
            array(
                'name' => 'get-attendees',
                'route' => '/api/v1/summits/{id}/attendees',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-own-attendee',
                'route' => '/api/v1/summits/{id}/attendees/me',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'delete-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            array(
                'name' => 'update-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            array(
                'name' => 'get-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'add-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'delete-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'checking-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}/check-in',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'add-attendee',
                'route' => '/api/v1/summits/{id}/attendees',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            array(
                'name' => 'add-attendee-ticket',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/tickets',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            array(
                'name' => 'delete-attendee-ticket',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/tickets/{ticket_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            // speakers
            array(
                'name' => 'get-speakers',
                'route' => '/api/v1/summits/{id}/speakers',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'add-speaker-by-summit',
                'route' => '/api/v1/summits/{id}/speakers',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'update-speaker-by-summit',
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'add-speaker-photo',
                'route' => '/api/v1/speakers/{speaker_id}/photo',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'add-speaker',
                'route' => '/api/v1/speakers',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'update-speaker',
                'route' => '/api/v1/speakers/{speaker_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'delete-speaker',
                'route' => '/api/v1/speakers/{speaker_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'get-all-speakers',
                'route' => '/api/v1/speakers',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-speaker',
                'route' => '/api/v1/speakers/{speaker_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'merge-speakers',
                'route' => '/api/v1/speakers/merge/{speaker_from_id}/{speaker_to_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ),
            array(
                'name' => 'get-speaker-by-summit',
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'add-speaker-feedback',
                'route' => '/api/v1/summits/{id}/speakers/{speaker_id}/presentations/{presentation_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            // events
            array(
                'name' => 'get-events',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-events-csv',
                'route' => '/api/v1/summits/{id}/events/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-published-events',
                'route' => '/api/v1/summits/{id}/events/published',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-schedule-empty-spots',
                'route' => '/api/v1/summits/{id}/events/published/empty-spots',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-unpublished-events',
                'route' => '/api/v1/summits/{id}/events/unpublished',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-all-events',
                'route' => '/api/v1/summits/events',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-all-published-events',
                'route' => '/api/v1/summits/events/published',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-published-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/published',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'add-event',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteEventData, $current_realm)],
            ),
            array(
                'name' => 'update-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteEventData, $current_realm)],
            ),
            array(
                'name' => 'update-events',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteEventData, $current_realm)],
            ),
            array(
                'name' => 'publish-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ),
            array(
                'name' => 'publish-events',
                'route' => '/api/v1/summits/{id}/events/publish',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ),
            array(
                'name' => 'unpublish-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ),
            array(
                'name' => 'unpublish-events',
                'route' => '/api/v1/summits/{id}/events/publish',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
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
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'add-event-attachment',
                'route' => '/api/v1/summits/{id}/events/{event_id}/attachment',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'add-event-feedback-v2',
                'route' => '/api/v2/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'update-event-feedback-v2',
                'route' => '/api/v2/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'get-event-feedback',
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback/{attendee_id?}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'delete-rsvp',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}/rsvp',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            // locations
             [
                 'name' => 'get-locations',
                 'route' => '/api/v1/summits/{id}/locations',
                 'http_method' => 'GET',
                 'scopes' => [
                     sprintf(SummitScopes::ReadSummitData, $current_realm),
                     sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                 ],
             ],
            [
                'name' => 'get-locations-metadata',
                'route' => '/api/v1/summits/{id}/locations/metadata',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-venues',
                'route' => '/api/v1/summits/{id}/locations/venues',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-external-locations',
                'route' => '/api/v1/summits/{id}/locations/external-locations',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-hotels',
                'route' => '/api/v1/summits/{id}/locations/hotels',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-airports',
                'route' => '/api/v1/summits/{id}/locations/airports',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-location',
                'route' => '/api/v1/summits/{id}/locations/{location_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-location-events',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/events',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-location-published-events',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/events/published',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            // event types
            [
                'name' => 'get-event-types',
                'route' => '/api/v1/summits/{id}/event-types',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-event-types-csv',
                'route' => '/api/v1/summits/{id}/event-types/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-event-type-by-id',
                'route' => '/api/v1/summits/{id}/event-types/{event_type_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-event-type',
                'route' => '/api/v1/summits/{id}/event-types',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteEventTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'seed-default-event-types',
                'route' => '/api/v1/summits/{id}/event-types/seed-defaults',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteEventTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-event-type',
                'route' => '/api/v1/summits/{id}/event-types/{event_type_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteEventTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-event-type',
                'route' => '/api/v1/summits/{id}/event-types/{event_type_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteEventTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            //tracks
            [
                'name' => 'get-tracks',
                'route' => '/api/v1/summits/{id}/tracks',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-tracks-csv',
                'route' => '/api/v1/summits/{id}/tracks/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-track-by-id',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-track',
                'route' => '/api/v1/summits/{id}/tracks',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-track',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-track',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            // track groups
            array(
                'name' => 'get-track-groups',
                'route' => '/api/v1/summits/{id}/track-groups',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
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
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/video/{video_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'create-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteVideoData, $current_realm)],
            ),
            array(
                'name' => 'update-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteVideoData, $current_realm)],
            ),
            array(
                'name' => 'delete-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteVideoData, $current_realm)],
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
                'name' => 'delete-rsvp-member',
                'route' => '/api/v1/summits/{id}/members/{member_id}/schedule/{event_id}/rsvp',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ),
            array(
                'name' => 'remove-from-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/me/summits/events/favorites/delete', $current_realm)],
            ),
           [
                'name' => 'get-own-member-schedule',
                'route' => '/api/v1/summits/{id}/members/{member_id}/schedule',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/me/read', $current_realm)],
            ],
           [
                'name' => 'add-2-own-member-schedule',
                'route' => '/api/v1/summits/{id}/members/{member_id}/schedule/{event_id}',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
           [
                'name' => 'remove-from-own-member-schedule',
                'route' => '/api/v1/summits/{id}/members/{member_id}/schedule/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            // notifications
           [
                'name' => 'get-notifications',
                'route' => '/api/v1/summits/{id}/notifications',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadNotifications, $current_realm)
                ],
            ],
            // promo codes
            [
                'name' => 'get-promo-codes',
                'route' => '/api/v1/summits/{id}/promo-codes',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-promo-codes-csv',
                'route' => '/api/v1/summits/{id}/promo-codes/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-promo-code',
                'route' => '/api/v1/summits/{id}/promo-codes/{promo_code_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-promo-code',
                'route' => '/api/v1/summits/{id}/promo-codes',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WritePromoCodeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-promo-code',
                'route' => '/api/v1/summits/{id}/promo-codes/{promo_code_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WritePromoCodeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-promo-code',
                'route' => '/api/v1/summits/{id}/promo-codes/{promo_code_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WritePromoCodeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'send-promo-code-mail',
                'route' => '/api/v1/summits/{id}/promo-codes/{promo_code_id}/mail',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WritePromoCodeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-promo-codes-metadata',
                'route' => '/api/v1/summits/{id}/promo-codes/metadata',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            // summit speakers assistances
            [
                'name' => 'get-speaker-assistances-by-summit',
                'route' => '/api/v1/summits/{id}/speakers-assistances',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-speaker-assistances-by-summit-csv',
                'route' => '/api/v1/summits/{id}/speakers-assistances/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-speaker-assistance',
                'route' => '/api/v1/summits/{id}/speakers-assistances',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitSpeakerAssistanceData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-speaker-assistance',
                'route' => '/api/v1/summits/{id}/speakers-assistances/{assistance_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitSpeakerAssistanceData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-speaker-assistance',
                'route' => '/api/v1/summits/{id}/speakers-assistances/{assistance_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-speaker-assistance',
                'route' => '/api/v1/summits/{id}/speakers-assistances/{assistance_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitSpeakerAssistanceData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'send-speaker-assistance-mail',
                'route' => '/api/v1/summits/{id}/speakers-assistances/{assistance_id}/mail',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitSpeakerAssistanceData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
        ]);
    }

    private function seedMemberEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('members', [
               // members
               [
                    'name' => 'get-members',
                    'route' => '/api/v1/members',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/members/read', $current_realm)],
               ],
               [
                    'name'        => 'get-my-member',
                    'route'       => '/api/v1/members/me',
                    'http_method' => 'GET',
                    'scopes'      => [sprintf('%s/members/read/me', $current_realm)],
               ],
               [
                    'name'        => 'get-member-affiliations',
                    'route'       => '/api/v1/members/{member_id}/affiliations',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/members/read', $current_realm)],
               ],
               [
                    'name'        => 'update-member-affiliation',
                    'route'       => '/api/v1/members/{member_id}/affiliations/{affiliation_id}',
                    'http_method' => 'PUT',
                    'scopes'      => [
                        sprintf(SummitScopes::WriteMemberData, $current_realm)
                    ],
               ],
               [
                    'name'        => 'delete-member-affiliation',
                    'route'       => '/api/v1/members/{member_id}/affiliations/{affiliation_id}',
                    'http_method' => 'DELETE',
                    'scopes'      => [
                        sprintf(SummitScopes::WriteMemberData, $current_realm)
                    ],
               ],
               [
                    'name'        => 'delete-member-rsvp',
                    'route'       => '/api/v1/members/{member_id}/rsvp/{rsvp_id}',
                    'http_method' => 'DELETE',
                    'scopes'      => [
                        sprintf(SummitScopes::WriteMemberData, $current_realm)
                    ],
                ]
            ]
        );
    }

    private function seedTagsEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('tags', [
                // members
                [
                    'name' => 'get-tags',
                    'route' => '/api/v1/tags',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                        sprintf(SummitScopes::ReadSummitData, $current_realm),
                        sprintf('%s/tags/read', $current_realm)
                    ],
                ]
            ]
        );
    }

    private function seedCompaniesEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('companies', [
                // members
                [
                    'name' => 'get-companies',
                    'route' => '/api/v1/companies',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                        sprintf(SummitScopes::ReadSummitData, $current_realm),
                        sprintf('%s/companies/read', $current_realm)
                    ],
                ]
            ]
        );
    }

    private function seedGroupsEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('groups', [
                // members
                [
                    'name' => 'get-groups',
                    'route' => '/api/v1/groups',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                        sprintf(SummitScopes::ReadSummitData, $current_realm),
                        sprintf('%s/groups/read', $current_realm)
                    ],
                ]
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