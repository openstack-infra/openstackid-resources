<?php namespace ModelSerializers;
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
/**
 * Class PresentationSpeakerSummitAssistanceConfirmationRequestSerializer
 * @package ModelSerializers
 */
final class PresentationSpeakerSummitAssistanceConfirmationRequestSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'OnSitePhone' => 'on_site_phone:json_string',
        'Registered'  => 'registered:json_boolean',
        'Confirmed'   => 'is_confirmed:json_boolean',
        'CheckedIn'   => 'checked_in:json_boolean',
        'SummitId'    => 'summit_id:json_int',
        'SpeakerId'   => 'speaker_id:json_int',
    ];
}