<?php namespace App\Models\Foundation\Summit\Locations\Banners;
/**
 * Copyright 2018 OpenStack Foundation
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
use DateTime;
/**
 * @ORM\Entity
 * @ORM\Table(name="ScheduledSummitLocationBanner")
 * Class ScheduledSummitLocationBanner
 * @package App\Models\Foundation\Summit\Locations\Banners
 */
class ScheduledSummitLocationBanner extends SummitLocationBanner
{
    const ClassName = 'ScheduledSummitLocationBanner';

    /**
     * @return string
     */
    public function getClassName(){
        return ScheduledSummitLocationBanner::ClassName;
    }

    /**
     * @ORM\Column(name="StartDate", type="datetime")
     * @var DateTime
     */
    protected $start_date;

    /**
     * @ORM\Column(name="EndDate", type="datetime")
     * @var DateTime
     */
    protected $end_date;

    /**
     * @param DateTime $value
     * @return $this
     */
    public function setStartDate(DateTime $value)
    {
        $summit = $this->getLocation()->getSummit();
        if(!is_null($summit))
        {
            $value = $summit->convertDateFromTimeZone2UTC($value);
        }
        $this->start_date = $value;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLocalStartDate()
    {
        $res = null;
        if(!empty($this->start_date)) {
            $value  = clone $this->start_date;
            $summit = $this->getLocation()->getSummit();
            if(!is_null($summit))
            {
                $res = $summit->convertDateFromUTC2TimeZone($value);
            }
        }
        return $res;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @param DateTime $value
     * @return $this
     */
    public function setEndDate(DateTime $value)
    {
        $summit = $this->getLocation()->getSummit();
        if(!is_null($summit))
        {
            $value = $summit->convertDateFromTimeZone2UTC($value);
        }
        $this->end_date = $value;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLocalEndDate()
    {
        $res = null;
        if(!empty($this->end_date)) {
            $value  = clone $this->end_date;
            $summit = $this->getLocation()->getSummit();
            if(!is_null($summit))
            {
                $res = $summit->convertDateFromUTC2TimeZone($value);
            }
        }
        return $res;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->end_date;
    }


}