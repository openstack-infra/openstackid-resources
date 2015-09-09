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

use models\utils\SilverstripeBaseModel;

/**
 * Class SummitAttendeeTicket
 * @package models\summit
 */
final class SummitAttendeeTicket extends SilverstripeBaseModel
{
    protected $table = 'SummitAttendeeTicket';

    protected $array_mappings = array
    (
        'ID'                 => 'id:json_int',
        'ExternalOrderId'    => 'external_order_id:json_int',
        'ExternalAttendeeId' => 'external_attendee_id:json_int',
        'TicketBoughtDate'   => 'bought_date:datetime_epoch',
    );


    /**
     * @return SummitTicketType
     */
    public function ticket_type()
    {
        return $this->hasOne('models\summit\SummitTicketType', 'ID', 'TicketTypeID')->first();
    }

    /**
     * @return SummitAttendee
     */
    public function owner()
    {
        return $this->hasOne('models\summit\SummitAttendee', 'ID', 'SummitAttendeeID')->first();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $values = parent::toArray();
        $values['ticket_type_id'] = intval($this->ticket_type()->ID);
        return $values;
    }
}