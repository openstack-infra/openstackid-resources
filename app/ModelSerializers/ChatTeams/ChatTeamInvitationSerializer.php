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
use models\main\ChatTeamInvitation;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;

/**
 * Class ChatTeamInvitationSerializer
 * @package ModelSerializers\ChatTeams
 */
final class ChatTeamInvitationSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'TeamId'     => 'team_id:json_int',
        'InviteeId'  => 'invitee_id:json_int',
        'InviterId'  => 'inviter_id:json_int',
        'Permission' => 'permission:json_string',
        'IsAccepted' => 'is_accepted:json_boolean',
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
        $invitation = $this->object;
        if(! $invitation instanceof ChatTeamInvitation) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);
        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'inviter':{
                        if(isset($values['inviter_id']))
                        {
                            unset($values['inviter_id']);
                            $values['inviter'] =  SerializerRegistry::getInstance()->getSerializer($invitation->getInviter())->serialize();
                        }
                    }
                    break;
                    case 'invitee':{
                        if(isset($values['invitee_id']))
                        {
                            unset($values['invitee_id']);
                            $values['invitee'] =  SerializerRegistry::getInstance()->getSerializer($invitation->getInvitee())->serialize();
                        }
                    }
                    break;
                    case 'team':{
                        if(isset($values['team_id']))
                        {
                            unset($values['team_id']);
                            $values['team'] =  SerializerRegistry::getInstance()->getSerializer($invitation->getTeam())->serialize($expand = 'owner,members,member');
                        }
                    }
                    break;
                }
            }
        }
        return $values;
    }

}