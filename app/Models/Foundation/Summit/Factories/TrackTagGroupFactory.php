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
use App\Models\Foundation\Summit\TrackTagGroup;
use models\summit\Summit;
/**
 * Class TrackTagGroupFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class TrackTagGroupFactory
{
    /**
     * @param Summit $summit
     * @param array $data
     * @return TrackTagGroup|null
     */
    public static function build(Summit $summit, array $data){
        $track_tag_group = new TrackTagGroup();
        if(is_null($track_tag_group)) return null;
        return self::populate($track_tag_group, $summit, $data);
    }

    /**
     * @param TrackTagGroup $track_tag_group
     * @param Summit $summit
     * @param array $data
     * @return TrackTagGroup
     */
    public static function populate(TrackTagGroup $track_tag_group, Summit $summit, array $data){

        if(isset($data['name']))
            $track_tag_group->setName(trim($data['name']));

        if(isset($data['label']))
            $track_tag_group->setLabel(trim($data['label']));

        if(isset($data['is_mandatory'])){
            $track_tag_group->setIsMandatory(boolval($data['is_mandatory']));
        }

        return $track_tag_group;
    }
}