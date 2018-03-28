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
use models\exceptions\ValidationException;
use models\summit\PresentationCategoryGroup;
use models\summit\PrivatePresentationCategoryGroup;
use models\summit\Summit;
/**
 * Class PresentationCategoryGroupFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class PresentationCategoryGroupFactory
{

    /**
     * @param Summit $summit
     * @param array $data
     * @return null
     * @throws ValidationException
     */
    public static function build(Summit $summit, array $data){
        if(!isset($data['class_name']))
            throw new ValidationException("missing class_name param");
        $track_group = null;
        switch($data['class_name']){
            case PresentationCategoryGroup::ClassName :{
                $track_group = self::populatePresentationCategoryGroup(new PresentationCategoryGroup, $data);
            }
            break;
            case PrivatePresentationCategoryGroup::ClassName :{
                $track_group = self::populatePrivatePresentationCategoryGroup($summit, new PrivatePresentationCategoryGroup, $data);
            }
        }
        return $track_group;
    }


    /**
     * @param PresentationCategoryGroup $track_group
     * @param array $data
     * @return PresentationCategoryGroup
     */
    private static function populatePresentationCategoryGroup(PresentationCategoryGroup $track_group, array $data){
        if(isset($data['name']))
            $track_group->setName(trim($data['name']));

        if(isset($data['description']))
            $track_group->setDescription(trim($data['description']));

        if(isset($data['color']))
            $track_group->setColor(trim($data['color']));

        return $track_group;
    }

    /**
     * @param Summit $summit
     * @param PrivatePresentationCategoryGroup $track_group
     * @param array $data
     * @return PresentationCategoryGroup
     */
    private static function populatePrivatePresentationCategoryGroup
    (
        Summit $summit,
        PrivatePresentationCategoryGroup $track_group,
        array $data
    )
    {

        $track_group->setSummit($summit);

        if(isset($data['submission_begin_date'])) {
            $start_datetime = intval($data['submission_begin_date']);
            $start_datetime = new \DateTime("@$start_datetime");
            $start_datetime->setTimezone($summit->getTimeZone());
            $track_group->setSubmissionBeginDate($start_datetime);
        }

        if(isset($data['submission_end_date'])) {
            $end_datetime = intval($data['submission_end_date']);
            $end_datetime = new \DateTime("@$end_datetime");
            $end_datetime->setTimezone($summit->getTimeZone());
            $track_group->setSubmissionEndDate($end_datetime);
        }

        if(isset($data['max_submission_allowed_per_user']))
            $track_group->setMaxSubmissionAllowedPerUser(intval($data['max_submission_allowed_per_user']));

        return self::populatePresentationCategoryGroup($track_group, $data);
    }

    /**
     * @param Summit $summit
     * @param PresentationCategoryGroup $track_group
     * @param array $data
     * @return PresentationCategoryGroup
     */
    public static function populate(Summit $summit, PresentationCategoryGroup $track_group, array $data){
        if($track_group instanceof PrivatePresentationCategoryGroup){
            return self::populatePrivatePresentationCategoryGroup($summit, $track_group, $data);
        }
        else if($track_group instanceof PresentationCategoryGroup){
            return self::populatePresentationCategoryGroup($track_group, $data);
        }
        return $track_group;
    }
}