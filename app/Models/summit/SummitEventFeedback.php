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

use libs\utils\JsonUtils;
use models\utils\SilverstripeBaseModel;
use models\main\Member;
/**
 * Class SummitEventFeedback
 * @package models\summit
 */
class SummitEventFeedback extends SilverstripeBaseModel
{
    protected $table = 'SummitEventFeedback';

    protected $mtiClassType = 'concrete';

    protected $array_mappings = array
    (
        'ID'      => 'id:json_int',
        'Rate'    => 'rate:json_int',
        'Note'    => 'note:json_string',
        'OwnerID' => 'owner_id:json_int',
        'EventID' => 'event_id:json_int',
        'Created' => 'created_date:datetime_epoch',
    );

    /**
     * @return Member
     */
    public function owner()
    {
        return $this->hasOne('models\main\Member', 'ID', 'OwnerID')->first();
    }

    /**
     * @return SummitEvent
     */
    public function event()
    {
        return $this->hasOne('models\summit\SummitEvent', 'ID', 'EventID')->first();
    }

    /**
     * @param bool|false $show_attendee_info
     * @return array
     */
    public function toArray($show_attendee_info = false)
    {
        $data      = parent::toArray();
        $member_id = $data['owner_id'];
        unset($data['owner_id']);
        $attendee  = SummitAttendee::where('MemberID', '=', $member_id)->first();

        if (!is_null($attendee))
        {

            if ($show_attendee_info)
            {

                $member = $attendee->member();
                $data['owner'] = array
                (
                    'id'         => intval($attendee->ID),
                    'first_name' => JsonUtils::toJsonString($member->FirstName),
                    'last_name'  => JsonUtils::toJsonString($member->Surname)
                );
            }
            else
            {
                $data['owner_id'] = intval($attendee->ID);
            }
        }
        else
        {
            $data['member_id'] =  intval($member_id);
        }
        return $data;
    }
}
