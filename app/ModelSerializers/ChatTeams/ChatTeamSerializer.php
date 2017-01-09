<?php namespace ModelSerializers\ChatTeams;

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

use models\main\ChatTeam;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;

/**
 * Class ChatTeamSerializer
 */
final class ChatTeamSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'        => 'name:json_string',
        'Description' => 'description:json_string',
        'OwnerId'     => 'owner_id:json_int',
        'Created'     => 'created_at:datetime_epoch',
        'LastEdited'  => 'updated_at:datetime_epoch',
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
        $team = $this->object;
        if(! $team instanceof ChatTeam) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);
        $members = [];

        foreach($team->getMembers() as $member){
            $members[] = SerializerRegistry::getInstance()->getSerializer($member)->serialize($expand);
        }

        $values['members'] = $members;

        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'owner':{
                        if(isset($values['owner_id']))
                        {
                            unset($values['owner_id']);
                            $values['owner'] =  SerializerRegistry::getInstance()->getSerializer($team->getOwner())->serialize();
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}