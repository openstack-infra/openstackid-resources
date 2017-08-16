<?php namespace services\apis;
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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use models\main\PushNotificationMessagePriority;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Class FireBaseGCMApi
 * @package services\apis
 */
final class FireBaseGCMApi implements IPushNotificationApi
{
    const BaseUrl = 'https://fcm.googleapis.com';
    /**
     * @var string
     */
    private $api_server_key;

    public function __construct($api_server_key)
    {
        $this->api_server_key = $api_server_key;
    }

    /**
     * https://firebase.google.com/docs/cloud-messaging/concept-options#notifications_and_data_messages
     * @param string $to
     * @param array $data
     * @param string $priority
     * @param null|int $ttl
     * @return bool
     */
    function sendPush($to, array $data, $priority = PushNotificationMessagePriority::Normal, $ttl = null)
    {
        $endpoint = self::BaseUrl.'/fcm/send';
        $client   = new Client();
        $res      = true;

        try {
            foreach ($to as $recipient) {

                $message = [
                    'to'       => '/topics/' . $recipient,
                    'data'     => $data,
                ];

                $response = $client->post($endpoint, [
                    'headers' => [
                        'Authorization' => sprintf('key=%s', $this->api_server_key),
                    ],
                    'json' => $message
                ]);

                if ($response->getStatusCode() !== 200) $res = $res && false;
            }
            return $res;
        }
        catch (ClientException $ex1){
            Log::error($ex1->getMessage());
            return false;
        }
        catch(Exception $ex){
            Log::error($ex->getMessage());
            return false;
        }
    }
}