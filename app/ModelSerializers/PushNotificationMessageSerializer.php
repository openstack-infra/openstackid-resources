<?php namespace App\ModelSerializers;
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
use models\main\PushNotificationMessage;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class PushNotificationMessageSerializer
 * @package App\ModelSerializers
 */
class PushNotificationMessageSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Message'      => 'message:json_string',
        'Priority'     => 'priority:json_string',
        'Platform'     => 'platform:json_string',
        'Created'      => 'created:datetime_epoch',
        'SentDate'     => 'sent_date:datetime_epoch',
        'IsSent'       => 'is_sent:json_boolean',
        'Approved'     => 'approved:json_boolean',
        'OwnerId'      => 'owner_id:json_int',
        'ApprovedById' => 'approved_by_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [])
    {
        $notification = $this->object;
        if(! $notification instanceof PushNotificationMessage) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {

                    case 'owner': {
                         if(!$notification->hasOwner()) continue;
                         unset($values['owner_id']);
                         $values['owner'] = SerializerRegistry::getInstance()->getSerializer($notification->getOwner())->serialize();
                    }
                    break;
                    case 'approved_by': {
                        if(!$notification->hasApprovedBy()) continue;
                        unset($values['approved_by_id']);
                        $values['approved_by'] = SerializerRegistry::getInstance()->getSerializer($notification->getApprovedBy())->serialize();
                    }
                    break;

                }
            }
        }

        return $values;
    }
}