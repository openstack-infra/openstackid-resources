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
use models\main\Group;

/**
 * Class GroupSerializer
 * @package ModelSerializers
 */
final class GroupSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Title'       => 'title:json_string',
        'Description' => 'description:json_string',
    );

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $group = $this->object;
        if(! $group instanceof Group) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);
        $members = [];

        foreach($group->getMembers() as $member){
            $members[] = SerializerRegistry::getInstance()->getSerializer($member)->serialize();
        }
        $values['members'] = $members;
        return $values;
    }
}