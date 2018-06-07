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
use App\Models\Foundation\Summit\SelectionPlan;
use models\summit\Summit;
/**
 * Class SummitSelectionPlanFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitSelectionPlanFactory
{
    /**
     * @param array $data
     * @param Summit $summit
     * @return SelectionPlan
     */
    public static function build(array $data, Summit $summit){
        return self::populate(new SelectionPlan, $data, $summit);
    }

    /**
     * @param SelectionPlan $selection_plan
     * @param Summit $summit
     * @param array $data
     * @return SelectionPlan
     */
    public static function populate(SelectionPlan $selection_plan, array $data, Summit $summit){

        if(isset($data['name']))
            $selection_plan->setName(trim($data['name']));

        if(isset($data['is_enabled']))
            $selection_plan->setIsEnabled(boolval($data['is_enabled']));

        if(array_key_exists('submission_begin_date', $data) && array_key_exists('submission_end_date', $data)) {
            if (isset($data['submission_begin_date']) && isset($data['submission_end_date'])) {
                $start_datetime = intval($data['submission_begin_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());
                $end_datetime = intval($data['submission_end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
                $end_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $selection_plan->setSubmissionBeginDate($start_datetime);
                $selection_plan->setSubmissionEndDate($end_datetime);
            }
            else{
                $selection_plan->clearSubmissionDates();
            }
        }

        if(array_key_exists('voting_begin_date', $data) && array_key_exists('voting_end_date', $data)) {
            if (isset($data['voting_begin_date']) && isset($data['voting_end_date'])) {
                $start_datetime = intval($data['voting_begin_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());
                $end_datetime = intval($data['voting_end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
                $end_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $selection_plan->setVotingBeginDate($start_datetime);
                $selection_plan->setVotingEndDate($end_datetime);
            }
            else{
                $selection_plan->clearVotingDates();
            }
        }

        if(array_key_exists('selection_begin_date', $data) && array_key_exists('selection_end_date', $data)) {
            if (isset($data['selection_begin_date']) && isset($data['selection_end_date'])) {
                $start_datetime = intval($data['selection_begin_date']);
                $start_datetime = new \DateTime("@$start_datetime");
                $start_datetime->setTimezone($summit->getTimeZone());
                $end_datetime = intval($data['selection_end_date']);
                $end_datetime = new \DateTime("@$end_datetime");
                $end_datetime->setTimezone($summit->getTimeZone());

                // set local time from UTC
                $selection_plan->setSelectionBeginDate($start_datetime);
                $selection_plan->setSelectionEndDate($end_datetime);
            }
            else{
                $selection_plan->clearSelectionDates();
            }
        }

        return $selection_plan;
    }
}