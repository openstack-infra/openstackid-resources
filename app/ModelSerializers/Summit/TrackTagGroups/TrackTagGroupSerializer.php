<?php namespace App\ModelSerializers\Summit\TrackTagGroups;
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
 * @package App\ModelSerializers\Summit\TrackTagGroups
 */
final class TrackTagGroupSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Name'         => 'name:json_string',
        'Label'         => 'label:json_string',
        'Order'         => 'order:json_int',
        'isMandatory'  => 'is_mandatory:json_boolean',
        'SummitId'     => 'summit_id:json_int',
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
        $track_tag_group = $this->object;
        return $values;
    }
}