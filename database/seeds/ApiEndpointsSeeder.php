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
use App\Security\OrganizationScopes;
use App\Security\MemberScopes;
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
        $this->seedTagsEndpoints();
        $this->seedCompaniesEndpoints();
        $this->seedGroupsEndpoints();
        $this->seedOrganizationsEndpoints();
        $this->seedTrackQuestionTemplateEndpoints();
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
            [
                'name' => 'get-summits',
                'route' => '/api/v1/summits',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                ],
            ],
            [
                'name' => 'get-summits-all',
                'route' => '/api/v1/summits/all',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-summit-cached',
                'route' => '/api/v1/summits/{id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-summit-non-cached',
                'route' => '/api/v2/summits/{id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-summit',
                'route' => '/api/v1/summits',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                ],
            ],
            [
                'name' => 'update-summit',
                'route' => '/api/v1/summits/{id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                ],
            ],
            [
                'name' => 'delete-summit',
                'route' => '/api/v1/summits/{id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                ],
            ],
            [
                'name' => 'get-summit-entity-events',
                'route' => '/api/v1/summits/{id}/entity-events',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            // attendees
            [
                'name' => 'get-attendees',
                'route' => '/api/v1/summits/{id}/attendees',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-own-attendee',
                'route' => '/api/v1/summits/{id}/attendees/me',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ],
            [
                'name' => 'update-attendee',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ],
            [
                'name' => 'get-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            [
                'name' => 'delete-event-attendee-schedule',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
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
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            array(
                'name' => 'add-attendee-ticket',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/tickets',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ),
            [
                'name' => 'delete-attendee-ticket',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/tickets/{ticket_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ],
            [
                'name' => 'reassign-attendee-ticket',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/tickets/{ticket_id}/reassign/{other_member_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteAttendeesData, $current_realm),
                ],
            ],
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
                'name' => 'get-my-speaker',
                'route' => '/api/v1/speakers/me',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            array(
                'name' => 'get-my-speaker-presentations-by-role-by-selection-plan',
                'route' => '/api/v1/speakers/me/presentations/{role}/selection-plans/{selection_plan_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ),
            [
                'name' => 'add-speaker-2-my-presentation',
                'route' => '/api/v1/speakers/me/presentations/{presentation_id}/speakers/{speaker_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm)
                ],
            ],
            [
                'name' => 'remove-speaker-from-my-presentation',
                'route' => '/api/v1/speakers/me/presentations/{presentation_id}/speakers/{speaker_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm)
                ],
            ],
            [
                'name' => 'add-moderator-2-my-presentation',
                'route' => '/api/v1/speakers/me/presentations/{presentation_id}/moderators/{speaker_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm)
                ],
            ],
            [
                'name' => 'remove-moderators-from-my-presentation',
                'route' => '/api/v1/speakers/me/presentations/{presentation_id}/moderators/{speaker_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm)
                ],
            ],
            [
                'name' => 'create-my-speaker',
                'route' => '/api/v1/speakers/me',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm),
                ],
            ],
            [
                'name' => 'update-my-speaker',
                'route' => '/api/v1/speakers/me',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm),
                ],
            ],
            [
                'name' => 'add-my-speaker-photo',
                'route' => '/api/v1/speakers/me/photo',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteMySpeakersData, $current_realm),
                ],
            ],
            [
                'name' => 'merge-speakers',
                'route' => '/api/v1/speakers/merge/{speaker_from_id}/{speaker_to_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSpeakersData, $current_realm),
                ],
            ],
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
            [
                'name' => 'get-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-published-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/published',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-event',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteEventData, $current_realm)],
            ],
            [
                'name' => 'update-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteEventData, $current_realm)],
            ],
            [
                'name' => 'update-events',
                'route' => '/api/v1/summits/{id}/events',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteEventData, $current_realm)],
            ],
            [
                'name' => 'publish-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ],
            [
                'name' => 'publish-events',
                'route' => '/api/v1/summits/{id}/events/publish',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ],
            [
                'name' => 'unpublish-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}/publish',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ],
            [
                'name' => 'unpublish-events',
                'route' => '/api/v1/summits/{id}/events/publish',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::PublishEventData, $current_realm)],
            ],
            [
                'name' => 'delete-event',
                'route' => '/api/v1/summits/{id}/events/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/summits/delete-event', $current_realm)],
            ],
            [
                'name' => 'add-event-feedback',
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            [
                'name' => 'add-event-attachment',
                'route' => '/api/v1/summits/{id}/events/{event_id}/attachment',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            [
                'name' => 'add-event-feedback-v2',
                'route' => '/api/v2/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            [
                'name' => 'update-event-feedback-v2',
                'route' => '/api/v2/summits/{id}/events/{event_id}/feedback',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            [
                'name' => 'get-event-feedback',
                'route' => '/api/v1/summits/{id}/events/{event_id}/feedback/{attendee_id?}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-rsvp',
                'route' => '/api/v1/summits/{id}/attendees/{attendee_id}/schedule/{event_id}/rsvp',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
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
                'name' => 'add-location',
                'route' => '/api/v1/summits/{id}/locations',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-location',
                'route' => '/api/v1/summits/{id}/locations/{location_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-location',
                'route' => '/api/v1/summits/{id}/locations/{location_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
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
            // maps
            [
                'name' => 'add-location-map',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/maps',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-location-map',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/maps/{map_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'get-location-map',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/maps/{map_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-location-map',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/maps/{map_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            // images
            [
                'name' => 'add-location-image',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/images',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-location-image',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/images/{image_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'get-location-image',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/images/{image_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-location-image',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/images/{image_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            // banners
            [
                'name' => 'get-location-banners',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/banners',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-location-banner',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/banners',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm),
                    sprintf(SummitScopes::WriteLocationBannersData, $current_realm)
                ],
            ],
            [
                'name' => 'update-location-banner',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/banners/{banner_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm),
                    sprintf(SummitScopes::WriteLocationBannersData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-location-banner',
                'route' => '/api/v1/summits/{id}/locations/{location_id}/banners/{banner_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm),
                    sprintf(SummitScopes::WriteLocationBannersData, $current_realm)
                ],
            ],
            // venues
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
                'name' => 'add-venue',
                'route' => '/api/v1/summits/{id}/locations/venues',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-venue',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            // floors
            [
                'name' => 'get-venue-floor',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors/{floor_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-venue-floor',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-venue-floor',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors/{floor_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-venue-floor',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors/{floor_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            // rsvp templates
            [
                'name' => 'get-rsvp-templates',
                'route' => '/api/v1/summits/{id}/rsvp-templates',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-rsvp-template',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-rsvp-template',
                'route' => '/api/v1/summits/{id}/rsvp-templates',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            [
                'name' => 'get-rsvp-template-question-metadata',
                'route' => '/api/v1/summits/{id}/rsvp-templates/questions/metadata',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-rsvp-template',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-rsvp-template',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            // rsvp template questions
            [
                'name' => 'get-rsvp-template-question',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-rsvp-template-question',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            [
                'name' => 'update-rsvp-template-question',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-rsvp-template-question',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            // multi value questions
            [
                'name' => 'add-rsvp-template-question-value',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}/values',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            [
                'name' => 'get-rsvp-template-question-value',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}/values/{value_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-rsvp-template-question-value',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}/values/{value_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-rsvp-template-question-value',
                'route' => '/api/v1/summits/{id}/rsvp-templates/{template_id}/questions/{question_id}/values/{value_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteRSVPTemplateData, $current_realm)
                ],
            ],
            // rooms
            [
                'name' => 'get-venue-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/rooms/{room_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-venue-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/rooms',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-venue-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/rooms/{room_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-venue-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/rooms/{room_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            // floor rooms
            [
                'name' => 'get-venue-floor-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors/{floor_id}/rooms/{room_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-venue-floor-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors/{floor_id}/rooms',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-venue-floor-room',
                'route' => '/api/v1/summits/{id}/locations/venues/{venue_id}/floors/{floor_id}/rooms/{room_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            // external locations
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
                'name' => 'add-external-location',
                'route' => '/api/v1/summits/{id}/locations/external-locations',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-external-location',
                'route' => '/api/v1/summits/{id}/locations/external-locations/{external_location_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
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
                'name' => 'add-hotel',
                'route' => '/api/v1/summits/{id}/locations/hotels',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-hotel',
                'route' => '/api/v1/summits/{id}/locations/hotels/{hotel_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
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
                'name' => 'add-airport',
                'route' => '/api/v1/summits/{id}/locations/airports',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-airport',
                'route' => '/api/v1/summits/{id}/locations/airports/{airport_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteLocationsData, $current_realm)
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
                'name' => 'get-track-extra-questions',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}/extra-questions',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-track-extra-questions',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}/extra-questions/{question_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'remove-track-extra-questions',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}/extra-questions/{question_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-track-allowed-tags',
                'route' => '/api/v1/summits/{id}/tracks/{track_id}/allowed-tags',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'copy-tracks-to-summit',
                'route' => '/api/v1/summits/{id}/tracks/copy/{to_summit_id}',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
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
            // ticket types
            [
                'name' => 'get-ticket-types',
                'route' => '/api/v1/summits/{id}/ticket-types',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-ticket-types-csv',
                'route' => '/api/v1/summits/{id}/ticket-types/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-ticket-type',
                'route' => '/api/v1/summits/{id}/ticket-types',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteTicketTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'seed-default-ticket-types',
                'route' => '/api/v1/summits/{id}/ticket-types/seed-defaults',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteTicketTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-ticket-type',
                'route' => '/api/v1/summits/{id}/ticket-types/{ticket_type_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteTicketTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-ticket-type',
                'route' => '/api/v1/summits/{id}/ticket-types/{ticket_type_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteTicketTypeData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-ticket-type',
                'route' => '/api/v1/summits/{id}/ticket-types/{ticket_type_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            // track groups
            [
                'name' => 'get-track-groups',
                'route' => '/api/v1/summits/{id}/track-groups',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-track-groups-csv',
                'route' => '/api/v1/summits/{id}/track-groups/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-track-groups-metadata',
                'route' => '/api/v1/summits/{id}/track-groups/metadata',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-track-group',
                'route' => '/api/v1/summits/{id}/track-groups',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'associate-track-2-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}/tracks/{track_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'disassociate-track-2-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}/tracks/{track_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'associate-group-2-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}/allowed-groups/{group_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'disassociate-group-2-track-group',
                'route' => '/api/v1/summits/{id}/track-groups/{track_group_id}/allowed-groups/{group_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteTrackGroupsData, $current_realm),
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
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
            // presentation submissions
            [
                'name' => 'submit-presentation',
                'route' => '/api/v1/summits/{id}/presentations',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteEventData, $current_realm),
                    sprintf(SummitScopes::WritePresentationData, $current_realm)
                ],
            ],
            [
                'name' => 'update-submit-presentation',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteEventData, $current_realm),
                    sprintf(SummitScopes::WritePresentationData, $current_realm)
                ],
            ],
            [
                'name' => 'complete-submit-presentation',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/completed',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteEventData, $current_realm),
                    sprintf(SummitScopes::WritePresentationData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-submit-presentation',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteEventData, $current_realm),
                    sprintf(SummitScopes::WritePresentationData, $current_realm)
                ],
            ],
            //videos
            [
                'name' => 'get-presentation-videos',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/video/{video_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadSummitData, $current_realm),
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'create-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos',
                'http_method' => 'POST',
                'scopes' => [sprintf(SummitScopes::WriteVideoData, $current_realm)],
            ],
            [
                'name' => 'update-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'PUT',
                'scopes' => [sprintf(SummitScopes::WriteVideoData, $current_realm)],
            ],
            [
                'name' => 'delete-presentation-video',
                'route' => '/api/v1/summits/{id}/presentations/{presentation_id}/videos/{video_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteVideoData, $current_realm)],
            ],
            //members
            [
                'name' => 'get-own-member',
                'route' => '/api/v1/summits/{id}/members/{member_id}',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/me/read', $current_realm)],
            ],
            [
                'name' => 'get-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites',
                'http_method' => 'GET',
                'scopes' => [sprintf('%s/me/read', $current_realm)],
            ],
            [
                'name' => 'add-2-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites/{event_id}',
                'http_method' => 'POST',
                'scopes' => [sprintf('%s/me/summits/events/favorites/add', $current_realm)],
            ],
            [
                'name' => 'delete-rsvp-member',
                'route' => '/api/v1/summits/{id}/members/{member_id}/schedule/{event_id}/rsvp',
                'http_method' => 'DELETE',
                'scopes' => [sprintf(SummitScopes::WriteSummitData, $current_realm)],
            ],
            [
                'name' => 'remove-from-own-member-favorites',
                'route' => '/api/v1/summits/{id}/members/{member_id}/favorites/{event_id}',
                'http_method' => 'DELETE',
                'scopes' => [sprintf('%s/me/summits/events/favorites/delete', $current_realm)],
            ],
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
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                    sprintf(SummitScopes::ReadNotifications, $current_realm)
                ],
            ],
            [
                'name' => 'get-notifications-csv',
                'route' => '/api/v1/summits/{id}/notifications/csv',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                    sprintf(SummitScopes::ReadNotifications, $current_realm)
                ],
            ],
            [
                'name' => 'get-notification-by-id',
                'route' => '/api/v1/summits/{id}/notifications/{notification_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                    sprintf(SummitScopes::ReadNotifications, $current_realm)
                ],
            ],
            [
                'name' => 'add-notifications',
                'route' => '/api/v1/summits/{id}/notifications',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteNotifications, $current_realm)
                ],
            ],
            [
                'name' => 'approve-notification',
                'route' => '/api/v1/summits/{id}/notifications/{notification_id}/approve',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteNotifications, $current_realm)
                ],
            ],
            [
                'name' => 'unapprove-notification',
                'route' => '/api/v1/summits/{id}/notifications/{notification_id}/approve',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteNotifications, $current_realm)
                ],
            ],
            [
                'name' => 'delete-notification',
                'route' => '/api/v1/summits/{id}/notifications/{notification_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteNotifications, $current_realm)
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
            // selection plans
            [
                'name' => 'get-current-selection-plan-by-status',
                'route' => '/api/v1/summits/all/selection-plans/current/{status}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                    sprintf(SummitScopes::ReadSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-selection-plan-by-id',
                'route' => '/api/v1/summits/{id}/selection-plans/{selection_plan_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                    sprintf(SummitScopes::ReadSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'update-selection-plan',
                'route' => '/api/v1/summits/{id}/selection-plans/{selection_plan_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-selection-plan',
                'route' => '/api/v1/summits/{id}/selection-plans/{selection_plan_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-selection-plan',
                'route' => '/api/v1/summits/{id}/selection-plans',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'add-track-group-2-selection-plan',
                'route' => '/api/v1/summits/{id}/selection-plans/{selection_plan_id}/track-groups/{track_group_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'delete-track-group-2-selection-plan',
                'route' => '/api/v1/summits/{id}/selection-plans/{selection_plan_id}/track-groups/{track_group_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            // track tag groups
            [
                'name' => 'get-track-tag-groups',
                'route' => '/api/v1/summits/{id}/track-tag-groups',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'get-track-tag-groups-allowed-tags',
                'route' => '/api/v1/summits/{id}/track-tag-groups/all/allowed-tags',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'seed-track-tag-groups-allowed-tags',
                'route' => '/api/v1/summits/{id}/track-tag-groups/all/allowed-tags/{tag_id}/seed-on-tracks',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteTracksData, $current_realm),
                ],
            ],
            [
                'name' => 'get-track-tag-group',
                'route' => '/api/v1/summits/{id}/track-tag-groups/{track_tag_group_id}',
                'http_method' => 'GET',
                'scopes' => [
                    sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                ],
            ],
            [
                'name' => 'seed-default-track-tag-groups',
                'route' => '/api/v1/summits/{id}/track-tag-groups/seed-defaults',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteTrackTagGroupsData, $current_realm)
                ],
            ],
            [
                'name' => 'add-track-tag-group',
                'route' => '/api/v1/summits/{id}/track-tag-groups',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteTrackTagGroupsData, $current_realm)
                ],
            ],
            [
                'name' => 'update-track-tag-group',
                'route' => '/api/v1/summits/{id}/track-tag-groups/{track_tag_group_id}',
                'http_method' => 'PUT',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteTrackTagGroupsData, $current_realm)
                ]
            ],
            [
                'name' => 'delete-track-tag-group',
                'route' => '/api/v1/summits/{id}/track-tag-groups/{track_tag_group_id}',
                'http_method' => 'DELETE',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteTrackTagGroupsData, $current_realm)
                ]
            ],
            [
                'name' => 'copy-track-tag-group-allowed-tags-to-track',
                'route' => '/api/v1/summits/{id}/track-tag-groups/{track_tag_group_id}/allowed-tags/all/copy/tracks/{track_id}',
                'http_method' => 'POST',
                'scopes' => [
                    sprintf(SummitScopes::WriteSummitData, $current_realm),
                    sprintf(SummitScopes::WriteTracksData, $current_realm)
                ]
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
               // my member affiliations
                [
                    'name'        => 'get-my-member-affiliations',
                    'route'       => '/api/v1/members/me/affiliations',
                    'http_method' => 'GET',
                    'scopes' => [sprintf(MemberScopes::ReadMyMemberData, $current_realm)],
                ],
                [
                    'name'        => 'add-my-member-affiliation',
                    'route'       => '/api/v1/members/me/affiliations',
                    'http_method' => 'POST',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMyMemberData, $current_realm)
                    ],
                ],
                [
                    'name'        => 'update-my-member-affiliation',
                    'route'       => '/api/v1/members/me/affiliations/{affiliation_id}',
                    'http_method' => 'PUT',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMyMemberData, $current_realm)
                    ],
                ],
                [
                    'name'        => 'delete-my-member-affiliation',
                    'route'       => '/api/v1/members/me/affiliations/{affiliation_id}',
                    'http_method' => 'DELETE',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMyMemberData, $current_realm)
                    ],
                ],
               // member affiliations
               [
                    'name'        => 'get-member-affiliations',
                    'route'       => '/api/v1/members/{member_id}/affiliations',
                    'http_method' => 'GET',
                    'scopes' => [sprintf('%s/members/read', $current_realm)],
               ],
                [
                    'name'        => 'add-member-affiliation',
                    'route'       => '/api/v1/members/{member_id}/affiliations',
                    'http_method' => 'POST',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMemberData, $current_realm)
                    ],
                ],
               [
                    'name'        => 'update-member-affiliation',
                    'route'       => '/api/v1/members/{member_id}/affiliations/{affiliation_id}',
                    'http_method' => 'PUT',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMemberData, $current_realm)
                    ],
               ],
               [
                    'name'        => 'delete-member-affiliation',
                    'route'       => '/api/v1/members/{member_id}/affiliations/{affiliation_id}',
                    'http_method' => 'DELETE',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMemberData, $current_realm)
                    ],
               ],
               [
                    'name'        => 'delete-member-rsvp',
                    'route'       => '/api/v1/members/{member_id}/rsvp/{rsvp_id}',
                    'http_method' => 'DELETE',
                    'scopes'      => [
                        sprintf(MemberScopes::WriteMemberData, $current_realm)
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

    private function seedOrganizationsEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('organizations', [
                // organizations
                [
                    'name' => 'get-organizations',
                    'route' => '/api/v1/organizations',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(OrganizationScopes::ReadOrganizationData, $current_realm)
                    ],
                ]
            ]
        );

        $this->seedApiEndpoints('organizations', [
                // organizations
                [
                    'name' => 'add-organizations',
                    'route' => '/api/v1/organizations',
                    'http_method' => 'POST',
                    'scopes' => [
                        sprintf(OrganizationScopes::WriteOrganizationData, $current_realm)
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

    public function seedTrackQuestionTemplateEndpoints(){
        $current_realm = Config::get('app.url');

        $this->seedApiEndpoints('track-question-templates', [
                // track question templates
                [
                    'name' => 'get-track-question-templates',
                    'route' => '/api/v1/track-question-templates',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm),
                    ],
                ],
                [
                    'name' => 'add-track-question-templates',
                    'route' => '/api/v1/track-question-templates',
                    'http_method' => 'POST',
                    'scopes' => [
                        sprintf(SummitScopes::WriteSummitData, $current_realm),
                        sprintf(SummitScopes::WriteTrackQuestionTemplateData, $current_realm),
                    ],
                ],
                [
                    'name' => 'update-track-question-templates',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}',
                    'http_method' => 'PUT',
                    'scopes' => [
                        sprintf(SummitScopes::WriteSummitData, $current_realm),
                        sprintf(SummitScopes::WriteTrackQuestionTemplateData, $current_realm),
                    ],
                ],
                [
                    'name' => 'delete-track-question-templates',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}',
                    'http_method' => 'DELETE',
                    'scopes' => [
                        sprintf(SummitScopes::WriteSummitData, $current_realm),
                        sprintf(SummitScopes::WriteTrackQuestionTemplateData, $current_realm),
                    ],
                ],
                [
                    'name' => 'get-track-question-template',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                    ],
                ],
                [
                    'name' => 'get-track-question-templates-metadata',
                    'route' => '/api/v1/track-question-templates/metadata',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                    ],
                ],
                [
                    'name' => 'add-track-question-template-value',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}/values',
                    'http_method' => 'POST',
                    'scopes' => [
                        sprintf(SummitScopes::WriteSummitData, $current_realm),
                        sprintf(SummitScopes::WriteTrackQuestionTemplateData, $current_realm),
                    ],
                ],
                [
                    'name' => 'update-track-question-template-value',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}/values/{track_question_template_value_id}',
                    'http_method' => 'PUT',
                    'scopes' => [
                        sprintf(SummitScopes::WriteSummitData, $current_realm),
                        sprintf(SummitScopes::WriteTrackQuestionTemplateData, $current_realm),
                    ],
                ],
                [
                    'name' => 'delete-track-question-template-value',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}/values/{track_question_template_value_id}',
                    'http_method' => 'DELETE',
                    'scopes' => [
                        sprintf(SummitScopes::WriteSummitData, $current_realm),
                        sprintf(SummitScopes::WriteTrackQuestionTemplateData, $current_realm),
                    ],
                ],
                [
                    'name' => 'get-track-question-template-value',
                    'route' => '/api/v1/track-question-templates/{track_question_template_id}/values/{track_question_template_value_id}',
                    'http_method' => 'GET',
                    'scopes' => [
                        sprintf(SummitScopes::ReadAllSummitData, $current_realm)
                    ],
                ],
            ]
        );
    }

}