<?php namespace models\summit\CalendarSync\WorkQueue;
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
use models\main\Member;
use Doctrine\ORM\Mapping AS ORM;
/**
 * Class AdminScheduleSummitActionSyncWorkRequest
 * @ORM\Entity
 * @ORM\Table(name="AdminScheduleSummitActionSyncWorkRequest")
 * @package models\summit\CalendarSync\WorkQueue
 */
class AdminScheduleSummitActionSyncWorkRequest
    extends AbstractCalendarSyncWorkRequest
{
    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member", cascade={"persist"})
     * @ORM\JoinColumn(name="CreatedByID", referencedColumnName="ID")
     * @var Member
     */
    protected $created_by;

    /**
     * @return Member
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param Member $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
    }

    /**
     * @return int
     */
    public function getCreatedById(){
        try {
            return is_null($this->created_by) ? 0 :$this->created_by->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }


}