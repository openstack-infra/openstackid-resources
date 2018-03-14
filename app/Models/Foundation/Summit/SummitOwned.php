<?php namespace models\summit;
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

use Doctrine\ORM\Mapping AS ORM;

/**
 * Class SummitOwned
 * @package models\summit
 */
trait SummitOwned
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\Summit")
     * @ORM\JoinColumn(name="SummitID", referencedColumnName="ID")
     * @var Summit
     */
    protected $summit;

    public function setSummit($summit){
        $this->summit = $summit;
    }

    /**
     * @return Summit
     */
    public function getSummit(){
        return $this->summit;
    }

    /**
     * @return int
     */
    public function getSummitId(){
        try {
            return $this->summit->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    public function clearSummit(){
        $this->summit = null;
    }
}