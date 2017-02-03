<?php namespace ModelSerializers;

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

use models\main\Affiliation;

/**
 * Class AffiliationSerializer
 * @package ModelSerializers
 */
final class AffiliationSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [

        'StartDate'       => 'start_date:datetime_epoch',
        'EndDate'         => 'end_date:datetime_epoch',
        'OwnerId'         => 'owner_id:json_int',
        'IsCurrent'       => 'is_current:json_boolean',
        'OrganizationId'  => 'organization_id:json_int'
    ];

    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $affiliation = $this->object;
        if (!$affiliation instanceof Affiliation) return [];
        $values      = parent::serialize($expand, $fields, $relations, $params);
        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'organization':
                    {
                        unset($values['organization_id']);
                        $values['organization'] = SerializerRegistry::getInstance()->getSerializer($affiliation->getOrganization())->serialize($expand,[],['none']);
                    }
                    break;
                }
            }
        }
        return $values;
    }
}