<?php namespace ModelSerializers;
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

/**
 * Class SummitEventTypeSerializer
 * @package ModelSerializers
 */
class SummitEventTypeSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Type'                 => 'name:json_string',
        'ClassName'            => 'class_name:json_string',
        'Color'                => 'color:json_string',
        'BlackoutTimes'        => 'black_out_times:json_boolean',
        'UseSponsors'          => 'use_sponsors:json_boolean',
        'AreSponsorsMandatory' => 'are_sponsors_mandatory:json_boolean',
        'AllowsAttachment'     => 'allows_attachment:json_boolean',
        'Default'              => 'is_default:json_boolean',
    ];

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
        $color  = isset($values['color']) ? $values['color']:'';
        if(empty($color))
            $color = 'f0f0ee';
        if (strpos($color,'#') === false) {
            $color = '#'.$color;
        }
        $values['color'] = $color;
        return $values;
    }
}