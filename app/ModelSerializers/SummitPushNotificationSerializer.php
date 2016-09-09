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
use models\summit\SummitPushNotification;
use models\summit\SummitPushNotificationChannel;


/**
 * Class SummitPushNotificationSerializer
 * @package ModelSerializers
 */
final class SummitPushNotificationSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Channel'  => 'channel:json_string',
        'Message'  => 'message:json_string',
        'SentDate' => 'sent_date:datetime_epoch',
        'Created'  => 'created:datetime_epoch',
        'IsSent'   => 'is_sent:json_boolean',
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
        $notification = $this->object;
        if(! $notification instanceof SummitPushNotification) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);

        if($notification->getChannel() == SummitPushNotificationChannel::Event){
            $values['event'] = SerializerRegistry::getInstance()->getSerializer($notification->getSummitEvent())->serialize();
        }

        if($notification->getChannel() == SummitPushNotificationChannel::Group){
            $values['group'] = SerializerRegistry::getInstance()->getSerializer($notification->getGroup())->serialize();
        }
        return $values;
    }
}