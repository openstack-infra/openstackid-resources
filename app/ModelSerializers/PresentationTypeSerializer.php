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
 * Class PresentationEventTypeSerializer
 * @package ModelSerializers
 */
class PresentationTypeSerializer extends SummitEventTypeSerializer
{
    protected static $array_mappings = array
    (
        'MaxSpeakers'          => 'max_speakers:json_int',
        'MinSpeakers'          => 'min_speakers:json_int',
        'MaxModerators'        => 'max_moderators:json_int',
        'MinModerators'        => 'min_moderators:json_int',
        'UseSpeakers'          => 'use_speakers:json_boolean',
        'AreSpeakersMandatory' => 'are_speakers_mandatory:json_boolean',
        'UseModerator'         => 'use_moderator:json_boolean',
        'ModeratorMandatory'   => 'moderator_mandatory:json_boolean',
        'ModeratorLabel'       => 'moderator_label:json_string',
    );

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $values = parent::serialize($expand, $fields, $relations, $params);
        return $values;
    }
}