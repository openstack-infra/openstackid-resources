<?php namespace App\Events;
/**
 * Copyright 2016 OpenStack Foundation
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
use models\main\Member;
use models\summit\Summit;


/**
 * Class MyFavoritesAdd
 * @package App\Events
 */
class MyFavoritesAdd extends SummitEventAction
{
    /**
     * @var Member
     */
    protected $member;

    /**
     * @var Summit
     */
    protected $summit;

    /**
     * MyFavoritesAdd constructor.
     * @param Member $member
     * @param Summit $summit
     * @param int $event_id
     */
    public function __construct($member, $summit, $event_id){

        $this->member = $member;
        $this->summit = $summit;
        parent::__construct($event_id);
    }

    public function getMember(){ return $this->member; }

    public function getSummit(){ return $this->summit;}
}