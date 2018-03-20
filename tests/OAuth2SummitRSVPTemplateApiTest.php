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

final class OAuth2SummitRSVPTemplateApiTest extends ProtectedApiTest
{
    public function testGetSummitRSVPTemplates($summit_id = 23)
    {
        $params = [
            'id'       => $summit_id,
            'page'     => 1,
            'per_page' => 5,
            'order'    => '-id'
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitRSVPTemplatesApiController@getAllBySummit",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $rsvp_templates = json_decode($content);
        $this->assertTrue(!is_null($rsvp_templates));
        return $rsvp_templates;
    }

    public function testGetRSVPTemplateById($summit_id = 23){

        $templates = $this->testGetSummitRSVPTemplates($summit_id);

        $params = [
            'id'          => $summit_id,
            'template_id' => $templates->data[0]->id,
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "GET",
            "OAuth2SummitRSVPTemplatesApiController@getRSVPTemplate",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(200);

        $rsvp_template = json_decode($content);
        $this->assertTrue(!is_null($rsvp_template));
        return $rsvp_template;
    }

    public function testDeleteRSVPTemplate($summit_id = 23){

        $template = $this->testGetRSVPTemplateById($summit_id);

        $params = [
            'id'          => $summit_id,
            'template_id' => $template->id
        ];

        $headers =
            [
                "HTTP_Authorization" => " Bearer " . $this->access_token,
                "CONTENT_TYPE"       => "application/json"
            ];

        $response = $this->action
        (
            "DELETE",
            "OAuth2SummitRSVPTemplatesApiController@deleteRSVPTemplate",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);

    }

    public function testAddRSVPTemplateQuestionRSVPTextBoxQuestionTemplate($summit_id = 24){

        $templates_response = $this->testGetSummitRSVPTemplates($summit_id);
        $templates = $templates_response->data;

        $params = [
            'id'          => $summit_id,
            'template_id' => $templates[0]->id
        ];

        $name       = str_random(16).'_rsvp_question';
        $data       = [
            'name'          => $name,
            'label'         => 'test label',
            'initial_value' => 'test initial value',
            'is_mandatory'  => true,
            'class_name'    => \App\Models\Foundation\Summit\Events\RSVP\RSVPTextBoxQuestionTemplate::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitRSVPTemplatesApiController@addRSVPTemplateQuestion",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

        $question = json_decode($content);
        $this->assertTrue(!is_null($question));
        $this->assertTrue($question->initial_value == 'test initial value');
        return $question;
    }

    public function testAddRSVPTemplateQuestionRSVPDropDownQuestionTemplate($summit_id = 24){

        $templates_response = $this->testGetSummitRSVPTemplates($summit_id);
        $templates = $templates_response->data;

        $params = [
            'id'          => $summit_id,
            'template_id' => $templates[0]->id
        ];

        $name       = str_random(16).'_rsvp_question';
        $data       = [
            'name'                => $name,
            'label'               => 'test dropdown',
            'is_mandatory'        => true,
            'is_country_selector' => true,
            'empty_string'        => '--select a value',
            'class_name'          => \App\Models\Foundation\Summit\Events\RSVP\RSVPDropDownQuestionTemplate::ClassName,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitRSVPTemplatesApiController@addRSVPTemplateQuestion",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

        $question = json_decode($content);
        $this->assertTrue(!is_null($question));
        return $question;
    }

    public function testUpdateRSVPTemplateQuestion($summit_id = 24){

        $templates = $this->testGetSummitRSVPTemplates($summit_id);
        $template  = $templates->data[0];
        $question  = $template->questions[0];

        $params = [
            'id'          => $summit_id,
            'template_id' => $template->id,
            'question_id' => $question->id
        ];

        $data       = [
            'name'       => $question->name,
            'label'      => $question->label.' update!',
            'class_name' => $question->class_name
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitRSVPTemplatesApiController@updateRSVPTemplateQuestion",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);

        $question = json_decode($content);
        $this->assertTrue(!is_null($question));
        return $question;
    }

    public function testDeleteRSVPTemplateQuestion($summit_id = 24, $template_id = 13, $question_id = 85){

        $params = [
            'id'          => $summit_id,
            'template_id' => $template_id,
            'question_id' => $question_id
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "DELETE",
            "OAuth2SummitRSVPTemplatesApiController@deleteRSVPTemplateQuestion",
            $params,
            [],
            [],
            [],
            $headers
        );

        $content = $response->getContent();
        $this->assertResponseStatus(204);
    }

    public function testAddRSVPQuestionValue($summit_id = 24, $template_id = 13, $question_id = 86){

        $params = [
            'id'          => $summit_id,
            'template_id' => $template_id,
            'question_id' => $question_id
        ];

        $value      = str_random(16).'_value';
        $label      = str_random(16).'_label';

        $data       = [
            'value'      => $value,
            'label'      => $label,
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "POST",
            "OAuth2SummitRSVPTemplatesApiController@addRSVPTemplateQuestionValue",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $value   = json_decode($content);
        $this->assertTrue(!is_null($value));
        return $value;
    }

    public function testUpdateRSVPQuestionValue($summit_id = 24, $template_id = 13, $question_id = 86){

        $value  = $this->testAddRSVPQuestionValue($summit_id, $template_id, $question_id);

        $params = [
            'id'          => $summit_id,
            'template_id' => $template_id,
            'question_id' => $question_id,
            'value_id'    => $value->id
        ];

        $data       = [
            'order' => 3
        ];

        $headers = [
            "HTTP_Authorization" => " Bearer " . $this->access_token,
            "CONTENT_TYPE"        => "application/json"
        ];

        $response = $this->action(
            "PUT",
            "OAuth2SummitRSVPTemplatesApiController@updateRSVPTemplateQuestionValue",
            $params,
            [],
            [],
            [],
            $headers,
            json_encode($data)
        );

        $content = $response->getContent();
        $this->assertResponseStatus(201);
        $value   = json_decode($content);
        $this->assertTrue(!is_null($value));
        $this->assertTrue($value->order == 3);
        return $value;
    }
}