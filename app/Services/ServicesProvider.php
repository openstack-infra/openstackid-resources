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

use App;
use Config;
use Illuminate\Support\ServiceProvider;
use services\apis\EventbriteAPI;

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
        App::singleton('services\model\ISummitService', 'services\model\SummitService');
        App::singleton('services\model\IPresentationService', 'services\model\PresentationService');
        App::singleton('services\model\IChatTeamService', 'services\model\ChatTeamService');
        App::singleton('services\apis\IEventbriteAPI',   function(){
            $api = new EventbriteAPI();
            $api->setCredentials(array('token' => Config::get("server.eventbrite_oauth2_personal_token", null)));
            return $api;
        });
    }
}