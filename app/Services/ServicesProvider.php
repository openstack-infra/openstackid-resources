<?php namespace services;
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
use App\Services\Apis\GoogleGeoCodingAPI;
use App\Services\Apis\IGeoCodingAPI;
use App\Services\Model\AttendeeService;
use App\Services\Model\FolderService;
use App\Services\Model\IAttendeeService;
use App\Services\Model\IFolderService;
use App\Services\Model\ILocationService;
use App\Services\Model\IMemberService;
use App\Services\Model\IRSVPTemplateService;
use App\Services\Model\ISummitEventTypeService;
use App\Services\Model\ISummitTrackService;
use App\Services\Model\SummitLocationService;
use App\Services\Model\MemberService;
use App\Services\Model\RSVPTemplateService;
use App\Services\Model\SummitPromoCodeService;
use App\Services\Model\SummitTrackService;
use App\Services\SummitEventTypeService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use ModelSerializers\BaseSerializerTypeSelector;
use ModelSerializers\ISerializerTypeSelector;
use services\apis\EventbriteAPI;
use services\apis\FireBaseGCMApi;
use services\apis\IEventbriteAPI;
use services\apis\IPushNotificationApi;
use services\model\IPresentationService;
use services\model\ISpeakerService;
use services\model\ISummitPromoCodeService;
use libs\utils\ICacheService;
use services\model\ISummitService;
use services\model\PresentationService;
use services\model\SpeakerService;
use services\model\SummitService;
use services\utils\RedisCacheService;

/***
 * Class ServicesProvider
 * @package services
 */
final class ServicesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(ICacheService::class, RedisCacheService::class);

        App::singleton(\libs\utils\ITransactionService::class, function(){
            return new \services\utils\DoctrineTransactionService('ss');
        });

        App::singleton(\libs\utils\IEncryptionService::class, function(){
            return new \services\utils\EncryptionService(
                Config::get("server.ss_encrypt_key", ''),
                Config::get("server.ss_encrypt_cypher", '')
            );
        });

        // setting facade
        $this->app['encryption'] = App::share(function ($app) {
            return new \services\utils\EncryptionService(
                Config::get("server.ss_encrypt_key", ''),
                Config::get("server.ss_encrypt_cypher", '')
            );
        });

        App::singleton(ISerializerTypeSelector::class, BaseSerializerTypeSelector::class);

        App::singleton(ISummitService::class, SummitService::class);

        App::singleton(ISpeakerService::class, SpeakerService::class);

        App::singleton(IPresentationService::class, PresentationService::class);

        App::singleton('services\model\IChatTeamService', 'services\model\ChatTeamService');

        App::singleton(IEventbriteAPI::class,   function(){
            $api = new EventbriteAPI();
            $api->setCredentials(array('token' => Config::get("server.eventbrite_oauth2_personal_token", null)));
            return $api;
        });

        App::singleton(IPushNotificationApi::class,   function(){
            $api = new FireBaseGCMApi(Config::get("server.firebase_gcm_server_key", null));
            return $api;
        });

        App::singleton
        (
            IAttendeeService::class,
            AttendeeService::class
        );

        // work request pre processors

        App::singleton
        (
            'App\Services\Model\Strategies\ICalendarSyncWorkRequestPreProcessorStrategyFactory',
            'App\Services\Model\Strategies\CalendarSyncWorkRequestPreProcessorStrategyFactory'
        );

        App::when('App\Services\Model\MemberActionsCalendarSyncPreProcessor')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestQueueManager')
            ->give('App\Services\Model\MemberScheduleWorkQueueManager');

        App::when('App\Services\Model\AdminActionsCalendarSyncPreProcessor')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestQueueManager')
            ->give('App\Services\Model\AdminScheduleWorkQueueManager');


        // work request process services

        App::when('App\Services\Model\MemberActionsCalendarSyncProcessingService')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestPreProcessor')
            ->give('App\Services\Model\MemberActionsCalendarSyncPreProcessor');

        App::singleton
        (
            'App\Services\Model\IMemberActionsCalendarSyncProcessingService',
            'App\Services\Model\MemberActionsCalendarSyncProcessingService'
        );

        App::when('App\Services\Model\AdminActionsCalendarSyncProcessingService')
            ->needs('App\Services\Model\ICalendarSyncWorkRequestPreProcessor')
            ->give('App\Services\Model\AdminActionsCalendarSyncPreProcessor');

        App::singleton
        (
            'App\Services\Model\IAdminActionsCalendarSyncProcessingService',
            'App\Services\Model\AdminActionsCalendarSyncProcessingService'
        );

        App::singleton(
            IMemberService::class,
            MemberService::class
        );

        App::singleton
        (
            ISummitPromoCodeService::class,
            SummitPromoCodeService::class
        );

        App::singleton
        (
            ISummitEventTypeService::class,
            SummitEventTypeService::class
        );

        App::singleton
        (
            ISummitTrackService::class,
            SummitTrackService::class
        );

        App::singleton
        (
            ILocationService::class,
            SummitLocationService::class
        );

        App::singleton
        (
            IFolderService::class,
            FolderService::class
        );

        App::singleton
        (
            IRSVPTemplateService::class,
            RSVPTemplateService::class
        );

        App::singleton(IGeoCodingAPI::class,   function(){
            return new GoogleGeoCodingAPI
            (
                Config::get("server.google_geocoding_api_key", null)
            );
        });
    }
}