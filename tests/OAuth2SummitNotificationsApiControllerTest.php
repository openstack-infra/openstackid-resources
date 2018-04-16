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
}