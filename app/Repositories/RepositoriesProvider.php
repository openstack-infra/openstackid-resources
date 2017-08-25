<?php namespace App\Repositories;
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
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\ORM\Facades\EntityManager;
/**
 * Class RepositoriesProvider
 * @package repositories
 */
final class RepositoriesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {

        App::singleton(
            'App\Models\ResourceServer\IApiEndpointRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\ResourceServer\ApiEndpoint::class);
        });

        App::singleton(
            'App\Models\ResourceServer\IEndpointRateLimitByIPRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\ResourceServer\EndPointRateLimitByIP::class);
            });

        App::singleton(
            'models\summit\ISummitRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\Summit::class);
        });

        App::singleton(
            'models\summit\IEventFeedbackRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\SummitEventFeedback::class);
        });

        App::singleton(
            'models\summit\ISpeakerRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\PresentationSpeaker::class);
        });

        App::singleton(
            'models\summit\ISummitEventRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\SummitEvent::class);
        });

        App::singleton(
            'models\summit\ISummitEntityEventRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\SummitEntityEvent::class);
        });


        App::singleton(
            'models\main\IMemberRepository',
            function(){
                return  EntityManager::getRepository(\models\main\Member::class);
        });

        App::singleton(
            'models\summit\ISummitAttendeeRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\SummitAttendee::class);
        });

        App::singleton(
            'models\summit\ISummitAttendeeTicketRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\SummitAttendeeTicket::class);
        });

        App::singleton(
            'models\summit\ISummitNotificationRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\SummitPushNotification::class);
            });

        App::singleton(
            'models\main\ITagRepository',
            function(){
                return  EntityManager::getRepository(\models\main\Tag::class);
            });

        App::singleton(
            'models\main\IChatTeamRepository',
            function(){
                return  EntityManager::getRepository(\models\main\ChatTeam::class);
            });

        App::singleton(
            'models\main\IChatTeamInvitationRepository',
            function(){
                return  EntityManager::getRepository(\models\main\ChatTeamInvitation::class);
            });

        App::singleton(
            'models\main\IChatTeamPushNotificationMessageRepository',
            function(){
                return  EntityManager::getRepository(\models\main\ChatTeamPushNotificationMessage::class);
            });

        App::singleton(
            'models\summit\IRSVPRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\RSVP::class);
            });

        App::singleton(
            'models\summit\IAbstractCalendarSyncWorkRequestRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\CalendarSync\WorkQueue\AbstractCalendarSyncWorkRequest::class);
            });

        App::singleton(
            'models\summit\ICalendarSyncInfoRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\CalendarSync\CalendarSyncInfo::class);
            });

        App::singleton(
            'models\summit\IScheduleCalendarSyncInfoRepository',
            function(){
                return  EntityManager::getRepository(\models\summit\CalendarSync\ScheduleCalendarSyncInfo::class);
            });

        // Marketplace

        App::singleton(
            'App\Models\Foundation\Marketplace\IApplianceRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\Foundation\Marketplace\Appliance::class);
            });

        App::singleton(
            'App\Models\Foundation\Marketplace\IDistributionRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\Foundation\Marketplace\Distribution::class);
            });

        App::singleton(
            'App\Models\Foundation\Marketplace\IConsultantRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\Foundation\Marketplace\Consultant::class);
            });

        App::singleton(
            'App\Models\Foundation\Marketplace\IPrivateCloudServiceRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\Foundation\Marketplace\PrivateCloudService::class);
            });

        App::singleton(
            'App\Models\Foundation\Marketplace\IPublicCloudServiceRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\Foundation\Marketplace\PublicCloudService::class);
            });

        App::singleton(
            'App\Models\Foundation\Marketplace\IRemoteCloudServiceRepository',
            function(){
                return  EntityManager::getRepository(\App\Models\Foundation\Marketplace\RemoteCloudService::class);
            });
    }
}