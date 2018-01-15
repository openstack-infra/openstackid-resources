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
use App\Services\Model\AttendeeService;
use App\Services\Model\IAttendeeService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use ModelSerializers\BaseSerializerTypeSelector;
use ModelSerializers\ISerializerTypeSelector;
use services\apis\EventbriteAPI;
use services\apis\FireBaseGCMApi;
/***
 * Class ServicesProvider
 * @package services
 */
class ServicesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton('libs\utils\ICacheService', 'services\utils\RedisCacheService');

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

        App::singleton('services\model\ISummitService', 'services\model\SummitService');

        App::singleton('services\model\ISpeakerService', 'services\model\SpeakerService');

        App::singleton('services\model\IPresentationService', 'services\model\PresentationService');

        App::singleton('services\model\IChatTeamService', 'services\model\ChatTeamService');

        App::singleton('services\apis\IEventbriteAPI',   function(){
            $api = new EventbriteAPI();
            $api->setCredentials(array('token' => Config::get("server.eventbrite_oauth2_personal_token", null)));
            return $api;
        });

        App::singleton('services\apis\IPushNotificationApi',   function(){
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
    }
}