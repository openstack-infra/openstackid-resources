<?php
namespace App\ModelSerializers\Marketplace;
use App\Models\Foundation\Marketplace\CloudService;
use ModelSerializers\SerializerRegistry;

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
class CloudServiceSerializer extends OpenStackImplementationSerializer
{
    protected static $allowed_relations = [
        'data_centers',
        'data_center_regions',
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

        $cloud_service  = $this->object;
        if(!$cloud_service instanceof CloudService) return [];
        if(!count($relations)) $relations = $this->getAllowedRelations();
        $values           = parent::serialize($expand, $fields, $relations, $params);

        if(in_array('data_centers', $relations)){
            $res = [];
            foreach ($cloud_service->getDataCenters() as $dataCenter){
                $res[] = SerializerRegistry::getInstance()
                    ->getSerializer($dataCenter)
                    ->serialize($expand);
            }
            $values['data_centers'] = $res;
        }

        if(in_array('data_center_regions', $relations)){
            $res = [];
            foreach ($cloud_service->getDataCenterRegions() as $region){
                $res[] = SerializerRegistry::getInstance()
                    ->getSerializer($region)
                    ->serialize($expand);
            }
            $values['data_center_regions'] = $res;
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