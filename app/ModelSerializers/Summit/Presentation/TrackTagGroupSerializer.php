<?php namespace App\ModelSerializers\Summit\Presentation;
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
 * Class TrackTagGroupSerializer
 * @package App\ModelSerializers\Summit\Presentation
 */
final class TrackTagGroupSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'         => 'name:json_text',
        'Label'        => 'label:json_text',
        'Mandatory'    => 'is_mandatory:json_boolean',
        'Order'        => 'order:json_int',
        'SummitId'     => 'summit_id:json_int',
    );
}