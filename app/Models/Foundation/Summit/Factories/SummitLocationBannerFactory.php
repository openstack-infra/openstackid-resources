<?php namespace App\Models\Foundation\Summit\Factories;
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
use App\Models\Foundation\Summit\Locations\Banners\ScheduledSummitLocationBanner;
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner;
use models\exceptions\ValidationException;
use models\summit\Summit;
use models\summit\SummitAbstractLocation;

/**
 * Class SummitLocationBannerFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitLocationBannerFactory
{
    const MinBannerDisplayMinutes = 1;

    /**
     * @param Summit $summit
     * @param SummitAbstractLocation $location
     * @param array $data
     * @return ScheduledSummitLocationBanner|SummitLocationBanner|null
     * @throws ValidationException
     */
    public static function build(Summit $summit, SummitAbstractLocation $location, array $data){
        if(!isset($data['class_name']))
            throw new ValidationException("missing class_name param");
        $banner = null;
        switch($data['class_name']){
            case SummitLocationBanner::ClassName :{
                $banner = self::populateSummitLocationBanner($summit, $location, new SummitLocationBanner, $data);
            }
                break;
            case ScheduledSummitLocationBanner::ClassName :{
                $banner = self::populateScheduledSummitLocationBanner($summit, $location, new ScheduledSummitLocationBanner, $data);
            }
        }
        return $banner;
    }

    /**
     * @param Summit $summit
     * @param SummitAbstractLocation $location
     * @param SummitLocationBanner $banner
     * @param array $data
     * @return SummitLocationBanner
     */
    private static function populateSummitLocationBanner(Summit $summit, SummitAbstractLocation $location, SummitLocationBanner $banner, array $data){
        if(isset($data['title']))
            $banner->setTitle(trim($data['title']));

        if(isset($data['content']))
            $banner->setContent(trim($data['content']));

        if(isset($data['type']))
            $banner->setType(trim($data['type']));

        if(isset($data['enabled']))
            $banner->setEnabled(boolval($data['enabled']));

        $banner->setLocation($location);

        return $banner;
    }

    /**
     * @param Summit $summit
     * @param SummitAbstractLocation $location
     * @param ScheduledSummitLocationBanner $banner
     * @param array $data
     * @return ScheduledSummitLocationBanner
     * @throws ValidationException
     */
    private static function populateScheduledSummitLocationBanner(Summit $summit, SummitAbstractLocation $location, ScheduledSummitLocationBanner $banner, array $data){

        self::populateSummitLocationBanner($summit, $location, $banner, $data);

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $start_datetime = intval($data['start_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime = intval($data['end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());
            $interval_seconds = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
            $minutes          = $interval_seconds / 60;
            if ($minutes < self::MinBannerDisplayMinutes)
                throw new ValidationException
                (
                    sprintf
                    (
                        "schedule banner should last at least %s minutes  - current duration %s",
                        self::MinBannerDisplayMinutes,
                        $minutes
                    )
                );

            // set local time from UTC
            $banner->setStartDate($start_datetime);
            $banner->setEndDate($end_datetime);

            if(!$summit->isTimeFrameInsideSummitDuration($banner->getLocalStartDate(), $banner->getLocalEndDate())){
                throw new ValidationException
                (
                    sprintf
                    (
                        'start/end datetime must be between summit start/end datetime! (%s - %s)',
                        $summit->getLocalBeginDate()->format('Y-m-d H:i:s'),
                        $summit->getLocalEndDate()->format('Y-m-d H:i:s')
                    )
                );
            }
        }

        return $banner;
    }

    /**
     * @param Summit $summit
     * @param SummitAbstractLocation $location
     * @param SummitLocationBanner $banner
     * @param array $data
     * @return ScheduledSummitLocationBanner|SummitLocationBanner
     */
    public static function populate
    (
        Summit $summit,
        SummitAbstractLocation $location,
        SummitLocationBanner $banner,
        array $data
    )
    {

        if($banner instanceof ScheduledSummitLocationBanner){
            return self::populateScheduledSummitLocationBanner($summit, $location, $banner, $data);
        }
        else if($banner instanceof SummitLocationBanner){
            return self::populateSummitLocationBanner($summit, $location, $banner, $data);
        }
        return $banner;
    }
}