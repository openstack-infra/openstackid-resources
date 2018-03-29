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
use App\Models\Foundation\Summit\Events\RSVP\RSVPTemplate;
/**
 * Class SummitRSVPTemplateFactory
 * @package App\Models\Foundation\Summit\Factories
 */
final class SummitRSVPTemplateFactory
{
    /**
     * @param array $data
     * @return RSVPTemplate
     */
    public static function build(array $data){
        return self::populate(new RSVPTemplate, $data);
    }

    /**
     * @param RSVPTemplate $template
     * @param array $data
     * @return RSVPTemplate
     */
    public static function populate(RSVPTemplate $template, array $data){

        if(isset($data['title']))
            $template->setTitle(trim($data['title']));

        if(isset($data['is_enabled']))
            $template->setIsEnabled(boolval($data['is_enabled']));

        return $template;
    }
}