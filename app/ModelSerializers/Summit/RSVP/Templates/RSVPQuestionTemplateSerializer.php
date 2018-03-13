<?php namespace App\ModelSerializers\Summit\RSVP\Templates;
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
use ModelSerializers\SilverStripeSerializer;
/**
 * Class RSVPQuestionTemplateSerializer
 * @package App\ModelSerializers\Summit\RSVP\Templates
 */
class RSVPQuestionTemplateSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Name'      => 'name:json_string',
        'Label'     => 'label:json_string',
        'Order'     => 'order:json_int',
        'Mandatory' => 'is_mandatory:json_boolean',
        'ReadOnly'  => 'is_read_only:json_boolean',
        'ClassName' => 'class_name:json_string',
    ];
}