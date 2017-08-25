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
use App\Models\Foundation\Marketplace\ConsultantServiceOfferedType;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class ConsultantServiceOfferedTypeSerializer
 * @package App\ModelSerializers\Marketplace
 */
final class ConsultantServiceOfferedTypeSerializer extends SilverStripeSerializer
{
    /**
     * @var array
     */
    protected static $array_mappings = [

    ];

    protected static $allowed_relations = [
        'service_offered_type',
        'region',
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

        $service  = $this->object;
        if(!$service instanceof ConsultantServiceOfferedType) return [];
        if(!count($relations)) $relations = $this->getAllowedRelations();
        $values           = parent::serialize($expand, $fields, $relations, $params);

        if(in_array('service_offered_type', $relations)){
            $values['service_offered_type'] =  SerializerRegistry::getInstance()
                ->getSerializer($service->getServiceOffered())
                ->serialize();;
        }

        if(in_array('region', $relations)){
            $values['region'] =  SerializerRegistry::getInstance()
                ->getSerializer($service->getRegion())
                ->serialize();;
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