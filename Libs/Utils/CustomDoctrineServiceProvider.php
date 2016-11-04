<?php namespace libs\utils;
/**
 * Copyright 2016 OpenStack Foundation
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

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\BootChain;
use LaravelDoctrine\ORM\DoctrineServiceProvider;
use LaravelDoctrine\ORM\IlluminateRegistry;

/**
 * Class CustomDoctrineServiceProvider
 * @package libs\utils
 */
final class CustomDoctrineServiceProvider extends DoctrineServiceProvider
{
    /**
     * Register the manager registry
     */
    protected function registerManagerRegistry()
    {
        $this->app->singleton('registry', function ($app) {
            $registry = new IlluminateRegistry($app, $app->make(CustomEntityManagerFactory::class));

            // Add all managers into the registry
            foreach ($app->make('config')->get('doctrine.managers', []) as $manager => $settings) {
                $registry->addManager($manager, $settings);
            }

            return $registry;
        });

        // Once the registry get's resolved, we will call the resolve callbacks which were waiting for the registry
        $this->app->afterResolving('registry', function (ManagerRegistry $registry, Container $container) {
            $this->bootExtensionManager();

            BootChain::boot($registry);
        });

        $this->app->alias('registry', ManagerRegistry::class);
        $this->app->alias('registry', IlluminateRegistry::class);
    }

}