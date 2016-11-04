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

use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use LaravelDoctrine\ORM\EntityManagerFactory;

/**
 * Class CustomEntityManagerFactory
 * @package libs\utils
 */
final class CustomEntityManagerFactory extends EntityManagerFactory
{
    /**
     * @param Configuration $configuration
     */
    protected function setSecondLevelCaching(Configuration $configuration)
    {
        $second_level_cache_config = $this->config->get('doctrine.cache.second_level', []);

        if (!is_array($second_level_cache_config)) return;
        if (!isset($second_level_cache_config['enabled'])) return;
        if (!$second_level_cache_config['enabled']) return;

        $configuration->setSecondLevelCacheEnabled(true);

        $cacheConfig = $configuration->getSecondLevelCacheConfiguration();
        $regions_config = isset($second_level_cache_config['regions']) ? $second_level_cache_config['regions'] : [];

        if (is_array($regions_config) && count($regions_config) > 0) {

            $regions_configuration = new RegionsConfiguration
            (
                isset($second_level_cache_config['region_lifetime']) ? $second_level_cache_config['region_lifetime'] : 3600,
                isset($second_level_cache_config['region_lock_lifetime']) ? $second_level_cache_config['region_lock_lifetime'] : 60
            );

            foreach ($regions_config as $region_name => $region_config) {
                if (isset($region_config['lifetime']))
                    $regions_configuration->setLifetime($region_name, $region_config['lifetime']);

                if (isset($region_config['lock_lifetime']))
                    $regions_configuration->setLockLifetime($region_name, $region_config['lock_lifetime']);

            }

            $cacheConfig->setRegionsConfiguration($regions_configuration);
        }

        // Cache logger
        if (isset($second_level_cache_config['log_enabled']) && $second_level_cache_config['log_enabled']){
            $logger = new StatisticsCacheLogger();
            $cacheConfig->setCacheLogger($logger);
        }

        $factory = new DefaultCacheFactory
        (
            $cacheConfig->getRegionsConfiguration(),
            $this->cache->driver()
        );

        $file_lock_region_directory = isset($second_level_cache_config['file_lock_region_directory']) ?
            $second_level_cache_config['file_lock_region_directory'] :
            '/tmp';

        $factory->setFileLockRegionDirectory($file_lock_region_directory);

        $cacheConfig->setCacheFactory
        (
            $factory
        );

    }
}