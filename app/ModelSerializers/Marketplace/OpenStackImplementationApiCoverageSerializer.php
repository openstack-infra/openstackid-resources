<?php namespace App\ModelSerializers\Marketplace;
/**
 * Copyright 2017 OpenStack Foundation
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
use App\Models\Foundation\Marketplace\OpenStackImplementationApiCoverage;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class OpenStackImplementationApiCoverageSerializer
 * @package App\ModelSerializers\Marketplace
 */
final class OpenStackImplementationApiCoverageSerializer extends SilverStripeSerializer
{
    /**
     * @var array
     */
    protected static $array_mappings = [
        'Percent' => 'api_coverage:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $api_coverage  = $this->object;
        if(!$api_coverage instanceof OpenStackImplementationApiCoverage) return [];
        $values           = parent::serialize($expand, $fields, $relations, $params);
        if(!$api_coverage->hasReleaseSupportedApiVersion()) return $values;

        $release_api_version = $api_coverage->getReleaseSupportedApiVersion();
        if($release_api_version->hasApiVersion() && $release_api_version->getApiVersion()->hasComponent()){
            $values["component"] =  SerializerRegistry::getInstance()
                ->getSerializer($release_api_version->getApiVersion()->getComponent())
                ->serialize();
        }
        else if($release_api_version->hasComponent()){
            $values["component"] =  SerializerRegistry::getInstance()
                ->getSerializer($release_api_version->getComponent())
                ->serialize();
        }

        if($release_api_version->hasRelease()){
            $values["release"] =  SerializerRegistry::getInstance()
                ->getSerializer($release_api_version->getRelease())
                ->serialize();
        }

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {

                }
            }
        }
        return $values;
    }
}