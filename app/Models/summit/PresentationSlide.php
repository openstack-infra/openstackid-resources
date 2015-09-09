<?php
/**
 * Copyright 2015 OpenStack Foundation
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

namespace models\summit;

use models\main\Image;

/**
 * Class PresentationSlide
 * @package models\summit
 */
class PresentationSlide extends PresentationMaterial
{
    protected $table = 'PresentationSlide';

    protected $mtiClassType = 'concrete';

    protected $array_mappings = array
    (
        'ID'             => 'id:json_int',
        'Name'           => 'name:json_text',
        'Description'    => 'description:json_text',
        'DisplayOnSite'  => 'display_on_site:json_boolean',
        'Featured'       => 'featured:json_boolean',
        'PresentationID' => 'presentation_id:json_int',
        'Link'           => 'link:json_text',
    );

    /**
     * @return Image
     */
    public function slide()
    {
        return $this->hasOne('models\main\Image', 'ID', 'SlideID')->first();
    }
}