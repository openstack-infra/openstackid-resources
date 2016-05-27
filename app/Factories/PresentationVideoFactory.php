<?php namespace factories;
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

use models\summit\factories\IPresentationVideoFactory;
use models\summit\PresentationVideo;

/**
 * Class PresentationVideoFactory
 * @package factories
 */
final class PresentationVideoFactory implements IPresentationVideoFactory
{
    /**
     * @param array $data
     * @return PresentationVideo
     */
    public function build(array $data){
        $video               = new PresentationVideo;
        $utc_now             = new \DateTime();
        $video->setYoutubeId(trim($data['you_tube_id']));
        $video->setDateUploaded($utc_now);

        if(isset($data['name']))
            $video->setName(trim($data['name']));

        if(isset($data['description']))
            $video->setDescription(trim($data['description']));

        $video->setDisplayOnSite(isset($data['display_on_site']) ? (bool)$data['display_on_site'] : true);

        return $video;
    }
}