<?php namespace ModelSerializers;
use models\summit\SummitAttendeeTicket;

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
final class SummitAttendeeTicketSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'ExternalOrderId'    => 'external_order_id:json_string',
        'ExternalAttendeeId' => 'external_attendee_id:json_string',
        'BoughtDate'         => 'bought_date:datetime_epoch',
        'TicketTypeId'       => 'ticket_type_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $ticket = $this->object;
        if (!$ticket instanceof SummitAttendeeTicket) return [];
        $values   = parent::serialize($expand, $fields, $relations, $params);
        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                    case 'ticket_type': {
                        unset($values['ticket_type_id']);
                        $values['ticket_type'] = SerializerRegistry::getInstance()->getSerializer($ticket->getTicketType())->serialize();
                    }
                    break;
                }
            }
        }

        return $values;
    }
}