<?php namespace App\Models\Foundation\Summit\Factories;
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
use models\summit\Summit;
use models\summit\SummitPushNotification;
/**
 * Class SummitPushNotificationFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitPushNotificationFactory
{
    /**
     * @param Summit $summit
     * @param array $data
     * @param array $params
     * @return SummitPushNotification
     */
    public static function build(Summit $summit, array $data, array $params)
    {

        $notification = new SummitPushNotification;

        if(isset($data['message']))
            $notification->setMessage(trim($data['message']));

        if(isset($data['channel']))
            $notification->setChannel(trim($data['channel']));

        if(isset($data['platform']))
            $notification->setPlatform(trim($data['platform']));

        if(isset($params['event']))
            $notification->setSummitEvent($params['event']);

        if(isset($params['group']))
            $notification->setGroup($params['group']);

        if(isset($params['owner']))
            $notification->setOwner($params['owner']);

        if(isset($params['recipients']))
        {
            foreach($params['recipients'] as $recipient)
                $notification->addRecipient($recipient);
        }

        $notification->setSummit($summit);

        return $notification;
    }
}