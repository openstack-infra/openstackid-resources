<?php

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
final class OAuth2ChatTeamApiTest extends ProtectedApiTest
{
    public function testAddTeam()
    {

        $params = [];

        $data = [
           'name' => 'team test #1',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2TeamsApiController@addTeam",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $team    = json_decode($content);
        $this->assertTrue(!is_null($team));
        $this->assertResponseStatus(201);
        return $team;
    }

    public function testUpdateTeam(){
        $team = $this->testAddTeam();

        $params  = ['team_id' => $team->id];
        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $data = [
            'name' => 'team test #1 update',
        ];

        $response = $this->action(
            "PUT",
            "OAuth2TeamsApiController@updateTeam",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );
        $this->assertResponseStatus(204);
    }

    public function testDeleteMyTeam(){
        $team = $this->testAddTeam();

        $params = ['team_id' => $team->id];
        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2TeamsApiController@deleteTeam",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $this->assertResponseStatus(204);
    }

    public function testGetMyTeams(){
        $params = ['expand' => 'owner, member'];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $response = $this->action(
            "GET",
            "OAuth2TeamsApiController@getMyTeams",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $teams = json_decode($content);
        $this->assertTrue(!is_null($teams));
        $this->assertResponseStatus(200);
    }

    public function testGetMyTeam($team_id = null, $expected_http_response = 200){

        if($team_id == null) {
            $team    = $this->testAddTeam();
            $team_id = $team->id;
        }

        $params  = ['team_id' => $team_id, 'expand' =>'owner,members,member'];
        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $response = $this->action(
            "GET",
            "OAuth2TeamsApiController@getMyTeam",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $team    = json_decode($content);
        $this->assertTrue(!is_null($team));
        $this->assertResponseStatus($expected_http_response);
    }

    public function testAddMemberToTeam(){
        $team = $this->testAddTeam();

        $params = [
            'member_id' => 11624,
            'team_id'   => $team->id,
        ];

        $data = [
            'permission' => \models\main\ChatTeamPermission::Read,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2TeamsApiController@addMember2MyTeam",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $invitation = json_decode($content);
        $this->assertTrue(!is_null($invitation));
        $this->assertResponseStatus(201);
        return $invitation;
    }

    public function testAcceptInvitation(){
        $invitation = $this->testAddMemberToTeam();

        $params = [
            'invitation_id' => $invitation->id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $response = $this->action(
            "PATCH",
            "OAuth2TeamInvitationsApiController@acceptInvitation",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content = $response->getContent();
        $team_member = json_decode($content);
        $this->assertTrue(!is_null($team_member));
        $this->assertResponseStatus(201);
        return $team_member;
    }

    public function testPostMessage($team = null){

        if(is_null($team))
            $team = $this->testAddTeam();

        $params = [
            'team_id'   => $team->id,
        ];

        $data = [
            'body' => 'test message',
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"       => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2TeamsApiController@postTeamMessage",
            $params,
            array(),
            array(),
            array(),
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $message = json_decode($content);
        $this->assertTrue(!is_null($message));
        $this->assertResponseStatus(201);
        return $message;
    }

    public function testGetMessagesFromTeam(){

        $team    = $this->testAddTeam();
        $message = $this->testPostMessage($team);

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $params = [
            'team_id' => $team->id,
            'expand'  => 'team,owner',
        ];

        $response = $this->action(
            "GET",
            "OAuth2TeamsApiController@getMyTeamMessages",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $content  = $response->getContent();
        $messages = json_decode($content);
        $this->assertTrue(!is_null($messages));
        $this->assertResponseStatus(200);
        return $messages;
    }

    public function testRemoveMemberFromTeam(){
        $team = $this->testAddTeam();

        $params = [
            'team_id'   => $team->id,
            'member_id' => $team->owner->id,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2TeamsApiController@removedMemberFromMyTeam",
            $params,
            array(),
            array(),
            array(),
            $headers
        );

        $this->assertResponseStatus(204);
        // try to get team again, we are not longer members , so will return 404
        $this->testGetMyTeam($team->id, 404);
    }
}