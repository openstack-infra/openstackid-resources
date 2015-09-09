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
 * Class SummitEntityEvent
 * @package models\summit
 */
final class SummitEntityEvent extends SilverstripeBaseModel
{
    protected $table = 'SummitEntityEvent';

    protected $array_mappings = array
    (
        'ID'              => 'id:json_int',
        'EntityID'        => 'entity_id:json_int',
        'EntityClassName' => 'entity_class:json_string',
        'Created'         => 'created:datetime_epoch',
        'Type'            => 'type',
    );

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return (bool)$this->Processed;
    }

    /**
     * @return $this
     */
    public function markProcessed()
    {
        $this->Processed = true;
        return $this;
    }
}