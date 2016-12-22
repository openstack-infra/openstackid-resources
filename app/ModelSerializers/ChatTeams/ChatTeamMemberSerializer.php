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
use models\main\ChatTeamMember;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;

/**
 * Class ChatTeamMemberSerializer
 * @package ModelSerializers\ChatTeams
 */
final class ChatTeamMemberSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'TeamId'     => 'team_id:json_int',
        'MemberId'   => 'member_id:json_int',
        'Permission' => 'permission:json_string',
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
        $team_member = $this->object;
        if(! $team_member instanceof ChatTeamMember) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'member':{
                        if(isset($values['member_id']))
                        {
                            unset($values['member_id']);
                            $values['member'] =  SerializerRegistry::getInstance()->getSerializer($team_member->getMember())->serialize();
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }

}