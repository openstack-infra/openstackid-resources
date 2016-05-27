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

use libs\utils\JsonUtils;

/**
 * Class SummitEventFeedbackSerializer
 * @package ModelSerializers
 */
final class SummitEventFeedbackSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Rate'    => 'rate:json_int',
        'Note'    => 'note:json_string',
        'Created' => 'created_date:datetime_epoch',
        'EventId' => 'event_id:json_int',
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
        $feedback = $this->object;
        $values   = parent::serialize($expand, $fields, $relations, $params);
        $event    = $feedback->getEvent();
        $member   = $feedback->hasOwner() ? $feedback->getOwner() : null;

        if (is_null($member)) return $values;

        $summit   = $event->getSummit();
        $attendee = $summit->getAttendeeByMemberId($member->getId());

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'owner': {

                        $owner = array
                        (
                            'id' => intval($member->getId()),
                            'first_name' => JsonUtils::toJsonString($member->getFirstName()),
                            'last_name' => JsonUtils::toJsonString($member->getLastName())
                        );

                        if (!is_null($attendee)) $owner['attendee_id'] = intval($attendee->getId());

                        $values['owner'] = $owner;
                    }
                    break;
                }
            }
        }

        if(!isset($values['owner'])) {
            $values['member_id'] = intval($member->getId());
            if (!is_null($attendee)) $values['attendee_id'] = intval($attendee->getId());
        }

        return $values;
    }
}