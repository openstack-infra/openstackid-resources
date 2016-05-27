<?php namespace repositories;

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
class RepositoriesProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
    }

    public function register()
    {
        App::singleton(
            'models\marketplace\IPublicCloudServiceRepository',
            'repositories\marketplace\EloquentPublicCloudServiceRepository'
        );

        App::singleton(
            'models\marketplace\IPrivateCloudServiceRepository',
            'repositories\marketplace\EloquentPrivateCloudServiceRepository'
        );
        App::singleton(
            'models\marketplace\IConsultantRepository',
            'repositories\marketplace\EloquentConsultantRepository'
        );
        App::singleton(
            'models\resource_server\IApiEndpointRepository',
            'repositories\resource_server\EloquentApiEndpointRepository'
        );

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

    }
}