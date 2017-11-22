<?php namespace App\ModelSerializers\CCLA;
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
use Models\Foundation\Main\CCLA\Team;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;

/**
 * Class TeamSerializer
 * @package App\ModelSerializers\CCLA
 */
final class TeamSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'          => 'name:json_string',
        'CompanyId'     => 'company_id:json_int',
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

        if(!$team instanceof Team) return [];

        $values         = parent::serialize($expand, $fields, $relations, $params);
        $members        = [];

        foreach($team->getMembers() as $member){
            $members[] = $member->getId();
        }

        $values['members'] = $members;

        if (!empty($expand)) {
            $expand_to = explode(',', $expand);
            foreach ($expand_to as $relation) {
                switch (trim($relation)) {
                    case 'company':{
                        if(isset($values['company_id']))
                        {
                            unset($values['company_id']);
                            $values['company'] =  SerializerRegistry::getInstance()->getSerializer($team->getCompany())->serialize($expand);
                        }
                    }
                    break;
                    case 'members':{
                        unset( $values['members']);
                        $members        = [];
                        foreach($team->getMembers() as $member){
                            $members[] = SerializerRegistry::getInstance()->getSerializer($member)->serialize($expand);
                        }

                        $values['members'] = $members;

                    }
                    break;
                }
            }
        }

        return $values;
    }
}