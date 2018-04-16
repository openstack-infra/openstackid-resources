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
use models\summit\Summit;
/**
 * Class SummitFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitFactory
{
    /**
     * @param array $data
     * @return Summit
     */
    public static function build(array $data){
        return self::populate(new Summit, $data);
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return Summit
     */
    public static function populate(Summit $summit, array $data){

        if(isset($data['name']) ){
            $summit->setName(trim($data['name']));
        }

        if(isset($data['time_zone_id']) ){
            $summit->setTimeZoneId(trim($data['time_zone_id']));
        }

        if(isset($data['active']) ){
            $summit->setActive(boolval($data['active']));
        }

        if(isset($data['available_on_api']) ){
            $summit->setAvailableOnApi(boolval($data['available_on_api']));
        }

        if(isset($data['dates_label']) ){
            $summit->setDatesLabel(trim($data['dates_label']));
        }

        if(isset($data['external_summit_id']) ){
            $summit->setExternalSummitId(trim($data['external_summit_id']));
        }

        if(isset($data['calendar_sync_name']) ){
            $summit->setCalendarSyncName(trim($data['calendar_sync_name']));
        }

        if(isset($data['calendar_sync_desc']) ){
            $summit->setCalendarSyncDesc(trim($data['calendar_sync_desc']));
        }

        if(isset($data['start_date']) && isset($data['end_date'])) {
            $start_datetime = intval($data['start_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime = intval($data['end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setBeginDate($start_datetime);
            $summit->setEndDate($end_datetime);
        }

        if(isset($data['submission_begin_date']) && isset($data['submission_end_date'])) {
            $start_datetime = intval($data['submission_begin_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime = intval($data['submission_end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setSubmissionBeginDate($start_datetime);
            $summit->setSubmissionEndDate($end_datetime);
        }

        if(isset($data['voting_begin_date']) && isset($data['voting_end_date'])) {
            $start_datetime = intval($data['voting_begin_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime = intval($data['voting_end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setVotingBeginDate($start_datetime);
            $summit->setVotingEndDate($end_datetime);
        }

        if(isset($data['selection_begin_date']) && isset($data['selection_end_date'])) {
            $start_datetime = intval($data['selection_begin_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime = intval($data['selection_end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setSelectionBeginDate($start_datetime);
            $summit->setSelectionEndDate($end_datetime);
        }

        if(isset($data['registration_begin_date']) && isset($data['registration_end_date'])) {
            $start_datetime = intval($data['registration_begin_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $end_datetime = intval($data['registration_end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setRegistrationBeginDate($start_datetime);
            $summit->setRegistrationEndDate($end_datetime);
        }

        if(isset($data['start_showing_venues_date'])) {
            $start_datetime = intval($data['start_showing_venues_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setStartShowingVenuesDate($start_datetime);
        }

        if(isset($data['schedule_start_date'])) {
            $start_datetime = intval($data['schedule_start_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());

            // set local time from UTC
            $summit->setScheduleDefaultStartDate($start_datetime);
        }

        if(isset($data['secondary_registration_link']) ){
            $summit->setSecondaryRegistrationLink(trim($data['secondary_registration_link']));
        }

        if(isset($data['secondary_registration_label']) ){
            $summit->setSecondaryRegistrationLabel(trim($data['secondary_registration_label']));
        }

        return $summit;
    }
}