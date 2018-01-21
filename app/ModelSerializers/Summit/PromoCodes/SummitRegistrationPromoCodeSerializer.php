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
use models\summit\SummitRegistrationPromoCode;
/**
 * Class SummitRegistrationPromoCodeSerializer
 * @package ModelSerializers
 */
class SummitRegistrationPromoCodeSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Code'        => 'code:json_string',
        'Redeemed'    => 'redeemed:json_boolean',
        'EmailSent'   => 'email_sent:json_boolean',
        'Source'      => 'source:json_string',
        'SummitId'    => 'summit_id:json_int',
        'CreatorId'   => 'creator_id:json_int',
        'ClassName'   => 'class_name:json_string',
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
        if(!$code instanceof SummitRegistrationPromoCode) return [];
        $values          = parent::serialize($expand, $fields, $relations, $params);
        $serializer_type = SerializerRegistry::SerializerType_Public;

        if(isset($params['serializer_type']))
            $serializer_type = $params['serializer_type'];

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'creator': {
                        if($code->hasCreator()){
                            unset($values['creator_id']);
                            $values['creator'] = SerializerRegistry::getInstance()->getSerializer
                            (
                                $code->getCreator(),
                                $serializer_type
                            )->serialize($expand);
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}