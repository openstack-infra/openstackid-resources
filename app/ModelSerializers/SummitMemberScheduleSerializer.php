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
 * Class SummitMemberScheduleSerializer
 * @package ModelSerializers
 */
final class SummitMemberScheduleSerializer extends SilverStripeSerializer
{

    protected static $array_mappings = array
    (

    );

    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $schedule                = $this->object;

        $values                  = SerializerRegistry::getInstance()->getSerializer($schedule->getEvent())->serialize
        (
            $expand,
            $fields,
            $relations,
            $params
        );

        return $values;
    }
}