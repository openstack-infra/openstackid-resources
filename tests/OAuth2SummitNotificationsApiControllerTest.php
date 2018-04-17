<?php
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

/**
 * Class OAuth2SummitNotificationsApiControllerTest
 */
final class OAuth2SummitNotificationsApiControllerTest extends ProtectedApiTest
{
    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetApprovedSummitNotifications($summit_id = 24)
    {
        $params = [
            'id' => $summit_id,
            'page' => 1,
            'per_page' => 15,
            'order' => '+sent_date',
            'expand' => 'owner,approved_by',
            'filter' => [
                'approved==1'
            ],
        ];

        $headers = [

            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitNotificationsApiController@getAll",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $notifications = json_decode($content);
        $this->assertTrue(!is_null($notifications));

        return $notifications;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testGetPushNotificationById($summit_id = 24){
        $notifications_response = $this->testGetApprovedSummitNotifications($summit_id);

        $params = [
            'id' => $summit_id,
            'notification_id' => $notifications_response->data[0]->id
        ];

        $headers = [

            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE" => "application/json"
        ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitNotificationsApiController@getById",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $notification = json_decode($content);
        $this->assertTrue(!is_null($notification));

        return $notification;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddPushNotificationEveryone($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $message = str_random(16).'_message';

        $data = [
            'message'  => $message,
            'channel' => \models\summit\SummitPushNotificationChannel::Everyone,
            'platform'   => \models\summit\SummitPushNotification::PlatformMobile,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitNotificationsApiController@addPushNotification",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $notification = json_decode($content);
        $this->assertTrue(!is_null($notification));
        return $notification;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddPushNotificationMembersFail($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $message = str_random(16).'_message';

        $data = [
            'message'  => $message,
            'channel' => \models\summit\SummitPushNotificationChannel::Members,
            'platform'   => \models\summit\SummitPushNotification::PlatformMobile,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitNotificationsApiController@addPushNotification",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(412);
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddPushNotificationMembers($summit_id = 24){

        $params = [
            'id' => $summit_id,
        ];

        $message = str_random(16).'_message';

        $data = [
            'message'  => $message,
            'channel' => \models\summit\SummitPushNotificationChannel::Members,
            'platform'   => \models\summit\SummitPushNotification::PlatformMobile,
            'recipient_ids' => [13867]
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitNotificationsApiController@addPushNotification",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $notification = json_decode($content);
        $this->assertTrue(!is_null($notification));
        return $notification;
    }
}