<?php namespace ModelSerializers;
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

/**
 * Class PresentationCategorySerializer
 * @package ModelSerializers
 */
final class PresentationCategorySerializer extends SilverStripeSerializer
{
    protected static $array_mappings =
    [
        'Title'                   => 'name:json_string',
        'Description'             => 'description:json_string',
        'Code'                    => 'code:json_string',
        'SessionCount'            => 'session_count:json_int',
        'AlternateCount'          => 'alternate_count:json_int',
        'LightningCount'          => 'lightning_count:json_int',
        'LightningAlternateCount' => 'lightning_alternate_count:json_int',
        'VotingVisible'           => 'voting_visible:json_boolean',
        'ChairVisible'            => 'chair_visible:json_boolean',
        'SummitId'                => 'summit_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [] )
    {
        $category = $this->object;
        $values   = parent::serialize($expand, $fields, $relations, $params);
        $groups   = [];

        foreach($category->getGroups() as $group){
            $groups[] = intval($group->getId());
        }
        $values['track_groups'] = $groups;

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'track_groups': {
                        $groups = [];
                        unset($values['track_groups']);
                        foreach ($category->getGroups() as $g) {
                            $groups[] = SerializerRegistry::getInstance()->getSerializer($g)->serialize(null, [], ['none']);
                        }
                        $values['track_groups'] = $groups;
                    }
                    break;
                }
            }
        }

        return $values;
    }
}