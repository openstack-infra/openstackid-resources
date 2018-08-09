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
use models\summit\PresentationCategory;
use models\summit\Summit;
/**
 * Class PresentationCategoryFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class PresentationCategoryFactory
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return PresentationCategory
     */
    public static function build(Summit $summit, array $data){
        $track = new PresentationCategory();
        self::populate($track, $data);
        return $track;
    }

    /**
     * @param PresentationCategory $track
     * @param array $data
     * @return PresentationCategory
     */
    public static function populate(PresentationCategory $track, array $data){
        if(isset($data['name']))
            $track->setTitle(trim($data['name']));

        if(isset($data['code']) && !empty($data['code']))
            $track->setCode(trim($data['code']));

        if(isset($data['description']))
            $track->setDescription(trim($data['description']));

        $track->calculateSlug();

        if(isset($data['session_count']))
            $track->setSessionCount(intval($data['session_count']));

        if(isset($data['alternate_count']))
            $track->setAlternateCount(intval($data['alternate_count']));

        if(isset($data['lightning_count']))
            $track->setLightningCount(intval($data['lightning_count']));

        if(isset($data['lightning_alternate_count']))
            $track->setLightningAlternateCount(intval($data['lightning_alternate_count']));

        if(isset($data['voting_visible']))
            $track->setVotingVisible(boolval($data['voting_visible']));

        if(isset($data['chair_visible']))
            $track->setChairVisible(boolval($data['chair_visible']));

        return $track;
    }

}