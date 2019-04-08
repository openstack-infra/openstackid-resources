<?php namespace App\Models\Foundation\Summit\Factories;
/**
 * Copyright 2019 OpenStack Foundation
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
use models\summit\PresentationLink;
/**
 * Class PresentationLinkFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class PresentationLinkFactory
{
    /**
     * @param array $data
     * @return PresentationLink
     */
    public static function build(array $data){
        return self::populate(new PresentationLink, $data);
    }

    /**
     * @param PresentationLink $slide
     * @param array $data
     * @return PresentationLink
     */
    public static function populate(PresentationLink $link, array $data){

        PresentationMaterialFactory::populate($link, $data);
        if(isset($data['link']))
            $link->setLink(trim($data['link']));

        return $link;
    }
}