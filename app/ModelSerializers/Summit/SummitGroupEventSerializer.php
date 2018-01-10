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
use models\summit\SummitGroupEvent;

/**
 * Class SummitGroupEventSerializer
 * @package ModelSerializers
 */
class SummitGroupEventSerializer extends SummitEventSerializer
{

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

        $event  = $this->object;
        if(!$event instanceof SummitGroupEvent) return [];

        $values['groups'] = $event->getGroupsIds();

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'groups': {
                        $groups = array();
                        unset($values['groups']);
                        foreach ($event->getGroups() as $g) {
                            $groups[] = SerializerRegistry::getInstance()->getSerializer($g)->serialize(null, [], ['none']);
                        }
                        $values['groups'] = $groups;
                    }
                    break;
                }
            }
        }

        return $values;
    }
}