<?php namespace models\summit;
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


use Doctrine\ORM\Mapping AS ORM;
use libs\utils\JsonUtils;
use models\utils\SilverstripeBaseModel;
use models\main\Member;
/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEventFeedback")
 * Class SummitEventFeedback
 * @package models\summit
 */
class SummitEventFeedback extends SilverstripeBaseModel
{


    protected static $array_mappings = array
    (
        'ID'      => 'id:json_int',
        'Rate'    => 'rate:json_int',
        'Note'    => 'note:json_string',
        'OwnerID' => 'owner_id:json_int',
        'EventID' => 'event_id:json_int',
        'Created' => 'created_date:datetime_epoch',
    );

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    private $owner;

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitEvent")
     * @ORM\JoinColumn(name="EventID", referencedColumnName="ID")
     * @var SummitEvent
     */
    private $event;

    /**
     * @return SummitEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param bool|false $expand_owner_info
     * @return array
     */
    public function toArray($expand_owner_info = false)
    {
        $data      = parent::toArray();
        $member_id = $data['owner_id'];
        unset($data['owner_id']);
        $member    = Member::where('ID', '=', $member_id)->first();
        if(is_null($member)) return $data;

        $attendee  = SummitAttendee::where('MemberID', '=', $member_id)->first();

        if($expand_owner_info){
            $owner = array
            (
                'id'         => intval($member->ID),
                'first_name' => JsonUtils::toJsonString($member->FirstName),
                'last_name'  => JsonUtils::toJsonString($member->Surname)
            );
            if (!is_null($attendee)) $owner['attendee_id'] = intval($attendee->ID);

            $data['owner'] = $owner;
        }
        else
        {
            $data['member_id'] =  intval($member->ID);
            if (!is_null($attendee)) $data['attendee_id'] = intval($attendee->ID);
        }

        return $data;
    }
}
