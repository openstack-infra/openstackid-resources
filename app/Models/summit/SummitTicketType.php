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
 * Class SummitTicketType
 * @package models\summit
 */
class SummitTicketType extends SilverstripeBaseModel
{
    protected $table = 'SummitTicketType';

    protected $array_mappings = array
    (
        'ID'          => 'id:json_int',
        'Name'        => 'name:json_string',
        'Description' => 'description:json_string',
    );

    public function allowed_summit_types()
    {
        return $this->belongsToMany
        (
            'models\summit\SummitType',
            'SummitTicketType_AllowedSummitTypes',
            'SummitTicketTypeID',
            'SummitTypeID'
        )->get();
    }


    private function getAllowedSummitTypeIds()
    {
        $ids = array();
        foreach($this->allowed_summit_types() as $type)
        {
            array_push($ids, intval($type->ID));
        }
        return $ids;
    }

    public function toArray()
    {
        $values = parent::toArray();
        $values['allowed_summit_types'] = $this->getAllowedSummitTypeIds();
        return $values;
    }
}