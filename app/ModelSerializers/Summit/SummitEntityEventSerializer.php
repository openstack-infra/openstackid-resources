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
 * Class SummitEntityEventSerializer
 * @package ModelSerializers
 */
final class SummitEntityEventSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'EntityId'        => 'entity_id:json_int',
        'EntityClassName' => 'class_name:json_string',
        'Type'            => 'type',
    );

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $entity_event = $this->object;
        $values       = parent::serialize($expand, $fields, $relations, $params);
        $entity       = $entity_event->getEntity();

        if(!is_null($entity))
        {
            $values['entity'] = SerializerRegistry::getInstance()->getSerializer($entity)->serialize
            (
                $expand,
                $fields,
                $relations,
                $params
            );
        }

        if($values['class_name'] == 'PresentationType')
            $values['class_name'] = 'SummitEventType';

        return $values;
    }
}