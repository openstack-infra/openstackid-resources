<?php namespace models\summit\factories;
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
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
use models\summit\SummitTicketType;
/**
 * Class SummitAttendeeTicketFactory
 * @package models\summit\factories
 */
final class SummitAttendeeTicketFactory
{

    /**
     * @param SummitAttendee $attendee
     * @param SummitTicketType $type
     * @param array $data
     * @return SummitAttendeeTicket
     */
    public static function build(SummitAttendee $attendee, SummitTicketType $type, array $data){
        $ticket = new SummitAttendeeTicket();
        $attendee->addTicket($ticket);
        if(isset($data['external_order_id']))
            $ticket->setExternalOrderId($data['external_order_id']);
        if(isset($data['external_attendee_id']))
            $ticket->setExternalAttendeeId($data['external_attendee_id']);

        $ticket->setTicketType($type);

        return $ticket;
    }
}