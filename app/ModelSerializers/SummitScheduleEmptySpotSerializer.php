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
use Libs\ModelSerializers\AbstractSerializer;

/**
 * Class SummitScheduleEmptySpotSerializer
 * @package ModelSerializers
 */
final class SummitScheduleEmptySpotSerializer extends AbstractSerializer
{
    protected static $array_mappings = [

        'LocationId'    => 'location_id:json_int',
        'StartDateTime' => 'start_date:datetime_epoch',
        'EndDateTime'   => 'end_date:datetime_epoch',
        'TotalMinutes'  => 'total_minutes:json_int',
    ];
}