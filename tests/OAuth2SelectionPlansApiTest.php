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

final class OAuth2SelectionPlansApiTest extends ProtectedApiTest
{
    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testAddSelectionPlan($summit_id = 24){
        $params = [
            'id' => $summit_id,
        ];

        $name       = str_random(16).'_selection_plan';
        $data = [
            'name'       => $name,
            'is_enabled'  => true
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitSelectionPlansApiController@addSelectionPlan",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $selection_plan = json_decode($content);
        $this->assertTrue(!is_null($selection_plan));
        $this->assertEquals($name, $selection_plan->name);
        return $selection_plan;
    }

    /**
     * @param int $summit_id
     * @return mixed
     */
    public function testUpdateSelectionPlan($summit_id = 24){
        $selection_plan = $this->testAddSelectionPlan($summit_id);
        $params = [
            'id' => $summit_id,
            'selection_plan_id' => $selection_plan->id
        ];

        $start = new DateTime('now');
        $end   = new DateTime('now');
        $end->add(new DateInterval('P15D'));

        $data = [
            'is_enabled'  => false,
            'submission_begin_date' => $start->getTimestamp(),
            'submission_end_date' => $end->getTimestamp(),
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitSelectionPlansApiController@updateSelectionPlan",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $selection_plan = json_decode($content);
        $this->assertTrue(!is_null($selection_plan));
        $this->assertEquals(false, $selection_plan->is_enabled);
        return $selection_plan;
    }

    /**
     * @param int $summit_id
     */
    public function testAddTrackGroupToSelectionPlan($summit_id = 24){

        $selection_plan = $this->testAddSelectionPlan($summit_id);

        $params = [
            'id'                => $summit_id,
            'selection_plan_id' => $selection_plan->id,
            'track_group_id'    => 1
        ];

        $headers = [
            "HTTP_Authorization"  => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitSelectionPlansApiController@addTrackGroupToSelectionPlan",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(404);
    }

    /**
     * @param string $status
     */
    public function testGetCurrentSelectionPlanByStatus($status = 'submission'){

        $params = [
           'status'  => $status,
            'expand' => 'track_groups,summit'
        ];

        $headers = [
            "HTTP_Authorization"  => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "GET",
            "OAuth2SummitSelectionPlansApiController@getCurrentSelectionPlanByStatus",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);
        $selection_plan = json_decode($content);
        $this->assertTrue(!is_null($selection_plan));
    }
}