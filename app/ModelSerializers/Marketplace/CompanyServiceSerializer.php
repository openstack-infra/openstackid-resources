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
use App\Models\Foundation\Marketplace\CompanyService;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class CompanyServiceSerializer
 * @package App\ModelSerializers\Marketplace
 */
class CompanyServiceSerializer extends SilverStripeSerializer
{
    /**
     * @var array
     */
    protected static $array_mappings = [
        'Name'      => 'name:json_string',
        'Overview'  => 'overview:json_string',
        'CompanyId' => 'company_id:json_int',
        'TypeId'    => 'type_id:json_int',
    ];

    protected static $allowed_relations = [
        'reviews',
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
        $company_service  = $this->object;
        if(!$company_service instanceof CompanyService) return [];
        $values           = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'company': {
                        unset($values['company_id']);
                        $values['company'] = SerializerRegistry::getInstance()->getSerializer($company_service->getCompany())->serialize(null, [], ['none']);;
                    }
                    break;
                    case 'type': {
                        unset($values['type_id']);
                        $values['type'] = SerializerRegistry::getInstance()->getSerializer($company_service->getType())->serialize(null, [], ['none']);;
                    }
                    break;
                    case 'reviews':
                    {
                        if(in_array('reviews', $relations)){
                            $reviews = [];
                            foreach ($company_service->getApprovedReviews() as $r) {
                                $reviews[] = SerializerRegistry::getInstance()->getSerializer($r)->serialize();
                            }
                            $values['reviews'] = $reviews;
                        }
                    }
                    break;
                }
            }
        }
        return $values;
    }
}