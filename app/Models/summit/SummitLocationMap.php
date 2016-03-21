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
use models\utils\SilverstripeBaseModel;
use Config;

/**
 * Class SummitLocationMap
 * @package models\summit
 */
class SummitLocationMap extends SummitLocationImage
{
    protected $stiBaseClass = 'models\summit\SummitLocationMap';

    protected $table = 'SummitLocationImage';

    protected $mtiClassType = 'concrete';

    protected $array_mappings = array
    (
        'ID'           => 'id:json_int',
        'LocationID'   => 'location_id:json_int',
        'Name'         => 'name:json_text',
        'Description'  => 'description:json_text',
        'ClassName'    => 'class_name:json_text',
        'Order'        => 'order:json_int',
    );

    /**
     * @return Image
     */
    public function map()
    {
        return $this->hasOne('models\main\Image', 'ID', 'PictureID')->first();
    }

    public function toArray()
    {
        $values = parent::toArray();
        $map    = $this->map();
        if(!is_null($map))
        {
            $values['image_url'] = Config::get("server.assets_base_url", 'https://www.openstack.org/'). $map->Filename;
        }
        return $values;
    }
}