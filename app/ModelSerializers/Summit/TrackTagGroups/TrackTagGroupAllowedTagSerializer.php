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
use App\Models\Foundation\Summit\TrackTagGroupAllowedTag;
use Libs\ModelSerializers\AbstractSerializer;
use ModelSerializers\SerializerRegistry;
/**
 * Class TrackTagGroupAllowedTagSerializer
 * @package App\ModelSerializers\Summit\TrackTagGroups
 */
final class TrackTagGroupAllowedTagSerializer extends AbstractSerializer
{
    protected static $array_mappings = [
        'Default' => 'is_default:json_boolean',
        'TrackTagGroupId' => 'track_tag_group_id:json_int',
        'TagId' => 'tag_id:json_int',
        'SummitId' => 'summit_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [])
    {
        $allowed_tag = $this->object;
        if (!$allowed_tag instanceof TrackTagGroupAllowedTag) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);


        if (!empty($expand)) {
            $relations = explode(',', $expand);
            foreach ($relations as $relation) {
                switch (trim($relation)) {

                    case 'track_tag_group':{
                        unset($values['track_tag_group_id']);
                        $values['track_tag_group'] = SerializerRegistry::getInstance()->getSerializer($allowed_tag->getTrackTagGroup())->serialize();
                    }
                        break;

                    case 'tag':{
                        unset($values['tag_id']);
                        $values['tag'] = SerializerRegistry::getInstance()->getSerializer($allowed_tag->getTag())->serialize();
                    }
                        break;

                }
            }
        }

        return $values;
    }
}