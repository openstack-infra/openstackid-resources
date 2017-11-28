<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace models\summit;

/**
 * Class SummitEventFactory
 * @package models\summit
 */
final class SummitEventFactory
{
    /**
     * @param Summit $summit
     * @param SummitEventType $type
     * @return SummitEvent
     */
    static public function build(Summit $summit, SummitEventType $type)
    {
        $event = new SummitEvent();

        if(PresentationType::IsPresentationEventType($summit, $type->getType()))
            $event = new Presentation();

        if(SummitEventType::isPrivate($type->getType()))
            $event = new SummitGroupEvent();

        if($type->isAllowsAttachment())
            $event = new SummitEventWithFile();

        return $event;
    }
}