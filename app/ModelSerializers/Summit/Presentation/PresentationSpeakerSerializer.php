<?php namespace ModelSerializers;
/**
 * Copyright 2019 OpenStack Foundation
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
use Libs\ModelSerializers\AbstractSerializer;
use models\summit\PresentationSpeaker;
/**
 * Class PresentationSpeakerSerializer
 * @package App\ModelSerializers\Summit\Presentation
 */
class PresentationSpeakerSerializer extends AbstractSerializer
{
    protected static $array_mappings = [
        'Role' => 'role:json_string',
        'PresentationId' => 'id:json_int'
    ];

    protected static $allowed_relations = [
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
        if (!count($relations)) $relations = $this->getAllowedRelations();

        $presentationSpeaker = $this->object;
        if (!$presentationSpeaker instanceof PresentationSpeaker) return [];

        $values = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'speaker':{
                        unset($values['id']);
                        $values = array_merge($values, SerializerRegistry::getInstance()->getSerializer($presentationSpeaker->getSpeaker())->serialize());
                    }
                    break;
                }
            }
        }

        return $values;
    }
}