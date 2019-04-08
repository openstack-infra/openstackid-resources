<?php namespace App\Models\Foundation\Summit\Factories;
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
use models\summit\PresentationVideo;
/**
 * Class PresentationVideoFactory
 * @package factories
 */
final class PresentationVideoFactory
{
    /**
     * @param array $data
     * @return PresentationVideo
     */
    public static function build(array $data){
        return self::populate(new PresentationVideo, $data);
    }

    /**
     * @param PresentationVideo $video
     * @param array $data
     * @return PresentationVideo
     */
    public static function populate(PresentationVideo $video, array $data){

        PresentationMaterialFactory::populate($video, $data);
        if(isset($data['you_tube_id']))
            $video->setYoutubeId(trim($data['you_tube_id']));
        if($video->getId() == 0)
            $video->setDateUploaded(new \DateTime());
        return $video;
    }
}