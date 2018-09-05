<?php namespace ModelSerializers;
/**
 * Copyright 2018 OpenStack Foundation
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
use models\summit\MemberSummitRegistrationPromoCode;
/**
 * Class MemberSummitRegistrationPromoCodeSerializer
 * @package ModelSerializers
 */
class MemberSummitRegistrationPromoCodeSerializer
    extends SummitRegistrationPromoCodeSerializer
{
    protected static $array_mappings = [
        'FirstName'   => 'first_name:json_string',
        'LastName'    => 'last_name:json_string',
        'Email'       => 'email:json_string',
        'Type'        => 'type:json_string',
        'OwnerId'     => 'owner_id:json_int',
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
        if(!count($relations)) $relations = $this->getAllowedRelations();

        $code            = $this->object;
        if(!$code instanceof MemberSummitRegistrationPromoCode) return [];
        $values          = parent::serialize($expand, $fields, $relations, $params);
        $serializer_type = SerializerRegistry::SerializerType_Public;

        if(isset($params['serializer_type']))
            $serializer_type = $params['serializer_type'];

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'owner': {
                        if($code->hasOwner()){
                            unset($values['owner_id']);
                            $values['owner'] = SerializerRegistry::getInstance()->getSerializer
                            (
                                $code->getOwner(),
                                $serializer_type
                            )->serialize($expand);
                        }
                    }
                    break;
                    case 'owner_name': {
                        if($code->hasOwner()){
                            $values['owner_name'] = $code->getOwner()->getFullName();
                        }
                    }
                    break;
                    case 'owner_email': {
                        if($code->hasOwner()){
                            $values['owner_email'] = $code->getOwner()->getEmail();
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}