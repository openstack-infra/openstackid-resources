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

use DB;
use models\utils\SilverstripeBaseModel;
use Config;
use libs\utils\JsonUtils;
/**
 * Class PresentationSpeaker
 * @package models\summit
 */
class PresentationSpeaker extends SilverstripeBaseModel
{
    protected $table = 'PresentationSpeaker';

    protected $array_mappings = array
    (
        'ID'            => 'id:json_int',
        'FirstName'     => 'first_name:json_string',
        'LastName'      => 'last_name:json_string',
        'Title'         => 'title:json_string',
        'Bio'           => 'bio:json_string',
        'IRCHandle'     => 'irc',
        'TwitterHandle' => 'twitter',
        'MemberID'      => 'member_id:json_int',
    );

    /**
     * @return Presentation[]
     */
    public function presentations()
    {
        return $this->belongsToMany('models\summit\Presentation','Presentation_Speakers','PresentationSpeakerID','PresentationID')->get();
    }

    public function getPresentationIds()
    {
        $ids = array();
        foreach($this->presentations() as $p)
        {
            array_push($ids, intval($p->ID));
        }
        return $ids;
    }

    /**
     * @return Image
     */
    public function photo()
    {
        return $this->hasOne('models\main\Image', 'ID', 'PhotoID')->first();
    }

    /**
     * @return Member
     */
    public function member()
    {
        return $this->hasOne('models\main\Member', 'ID', 'MemberID')->first();
    }

    public function toArray()
    {
        $values = parent::toArray();
        $values['presentations'] = $this->getPresentationIds();
        $member = $this->member();
        $values['pic'] = Config::get("server.assets_base_url", 'https://www.openstack.org/'). 'profile_images/speakers/'. $this->ID;
        if(!is_null($member))
        {
            $values['gender'] = $member->Gender;
        }
        return $values;
    }


    /**
     * @param int $presentation_id
     * @return Presentation
     */
    public function getPresentation($presentation_id)
    {
        return $this->belongsToMany('models\summit\Presentation','Presentation_Speakers','PresentationSpeakerID', 'PresentationID')
            ->where('PresentationID','=',$presentation_id)
            ->first();
    }
}