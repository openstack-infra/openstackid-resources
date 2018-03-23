<?php namespace App\Models\Foundation\Summit\Factories;
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
use models\summit\SummitTicketType;
/**
 * Class SummitTicketTypeFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitTicketTypeFactory
{
    /**
     * @param array $data
     * @return SummitTicketTypeFactory
     */
    public static function build(array $data){
        return self::populate(new SummitTicketType, $data);
    }

    /**
     * @param SummitTicketType $ticket_type
     * @param array $data
     * @return SummitTicketType
     */
    public static function populate(SummitTicketType $ticket_type, array $data){

        if(isset($data['name']))
            $ticket_type->setName(trim($data['name']));

        if(isset($data['description']))
            $ticket_type->setDescription(trim($data['description']));

        if(isset($data['external_id']))
            $ticket_type->setExternalId(trim($data['external_id']));

        return $ticket_type;
    }
}