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
     * @param string $title
     * @param string $description
     * @param \DateTime $start
     * @param \DateTime $end
     * @param $allow_feedback
     * @param SummitEventType $type
     * @param SummitAbstractLocation $location
     * @param Summit $summit
     * @return SummitEvent
     */
    static public function build
    (
        $title,
        $description,
        \DateTime $start,
        \DateTime $end,
        $allow_feedback,
        SummitEventType $type,
        SummitAbstractLocation $location,
        Summit $summit)
    {
        $event                = $type->Type === 'Presentation' ? new Presentation: new SummitEvent;
        $event->ClassName     = $event instanceof Presentation ? 'Presentation':'SummitEvent';
        $event->Title         = $title;
        $event->Description   = $description;
        $event->StartDate     = $start->format('Y-m-d H:i:s');
        $event->EndDate       = $end->format('Y-m-d H:i:s');
        $event->AllowFeedBack = $allow_feedback;
        $event->TypeID        = $type->ID;
        $event->LocationID    = $location->ID;
        $event->SummitID      = $summit->ID;
        return $event;
    }
}