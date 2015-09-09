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

class PresentationSpeaker extends SilverstripeBaseModel
{
    protected $table = 'PresentationSpeaker';

    protected $array_mappings = array
    (
        'ID'            => 'id',
        'FirstName'     => 'first_name:json_string',
        'LastName'      => 'last_name:json_string',
        'Title'         => 'title:json_string',
        'Bio'           => 'bio:json_string',
        'IRCHandle'     => 'irc',
        'TwitterHandle' => 'twitter',
        'MemberID'      => 'member_id',
    );

    public function presentations()
    {
        return $this->belongsToMany('models\summit\Presentation','Presentation_Speakers','PresentationSpeakerID','PresentationID')->get();
    }
}