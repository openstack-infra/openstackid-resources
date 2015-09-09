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
use libs\utils\JsonUtils;
use models\utils\SilverstripeBaseModel;
use Symfony\Component\Translation\Tests\Dumper\QtFileDumperTest;

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
        $member = $this->member();
        if(!is_null($member))
        {
            $values['gender'] = $member->Gender;
        }
        return $values;
    }

    /**
     * @return PresentationSpeakerFeedback[]
     */
    public function feedback()
    {
        return $this->hasMany('models\summit\PresentationSpeakerFeedback', 'SpeakerID', 'ID')->get();
    }

    public function addFeedBack(PresentationSpeakerFeedback $feedback)
    {
$insert_1 = <<<SQL
INSERT INTO `SummitEventFeedback`
(
`ClassName`,
`Created`,
`LastEdited`,
`Rate`,
`Note`,
`Approved`,
`ApprovedDate`,
`OwnerID`,
`ApprovedByID`,
`EventID`)
VALUES
(
'PresentationSpeakerFeedback',
UTC_TIMESTAMP(),
UTC_TIMESTAMP(),
?,
?,
0,
NULL,
?,
0,
?);
SQL;


        DB::connection('ss')->insert($insert_1, [$feedback->Rate, $feedback->Note, $feedback->OwnerID, $feedback->EventID]);

$insert_2 = <<<SQL
INSERT INTO `PresentationSpeakerFeedback`
(`ID`,
`SpeakerID`)
VALUES
(LAST_INSERT_ID(),
?);
SQL;
        DB::connection('ss')->insert($insert_2, [$this->ID]);

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