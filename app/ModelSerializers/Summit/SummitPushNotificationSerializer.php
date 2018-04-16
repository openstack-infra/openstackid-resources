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
use App\ModelSerializers\PushNotificationMessageSerializer;
use models\summit\SummitPushNotification;
use models\summit\SummitPushNotificationChannel;


/**
 * Class SummitPushNotificationSerializer
 * @package ModelSerializers
 */
final class SummitPushNotificationSerializer extends PushNotificationMessageSerializer
{
    protected static $array_mappings = [
        'Channel'  => 'channel:json_string',
        'SummitId' => 'summit_id:json_int',
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
        $notification = $this->object;
        if(! $notification instanceof SummitPushNotification) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);

        if($notification->getChannel() == SummitPushNotificationChannel::Event){
            $values['event'] = SerializerRegistry::getInstance()->getSerializer($notification->getSummitEvent())->serialize();
        }

        if($notification->getChannel() == SummitPushNotificationChannel::Group){
            $values['group'] = SerializerRegistry::getInstance()->getSerializer($notification->getGroup())->serialize();
        }

        if($notification->getChannel() == SummitPushNotificationChannel::Members){
            $values['recipients'] = [];
            foreach ($notification->getRecipients() as $recipient)
                $values['recipients'][] = SerializerRegistry::getInstance()->getSerializer($recipient)->serialize();
        }

        return $values;
    }
}