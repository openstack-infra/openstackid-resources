<?php
/**
 * Copyright 2017 OpenStack Foundation
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
use Illuminate\Support\Facades\Validator;

class ValidatorTest extends TestCase
{
    public function testSummitEventValidator(){
        $rules = [
            'title'           => 'sometimes|required|string|max:100',
            'description'     => 'sometimes|required|string',
            'social_summary'  => 'sometimes|string|max:100',
            'location_id'     => 'sometimes|integer',
            'start_date'      => 'sometimes|date_format:U',
            'end_date'        => 'sometimes|required_with:start_date|date_format:U|after:start_date',
            'allow_feedback'  => 'sometimes|boolean',
            'type_id'         => 'sometimes|required|integer',
            'track_id'        => 'sometimes|required|integer',
            'tags'            => 'sometimes|string_array',
        ];

        // Creates a Validator instance and validates the data.
        $validation = Validator::make(['title' => 'test','description'=> '' ], $rules);

        $res = $validation->fails();
        if($res){
            $messages = $validation->messages()->toArray();
            echo $messages;
        }
    }
}