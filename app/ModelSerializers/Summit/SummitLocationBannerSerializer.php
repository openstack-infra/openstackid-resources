<?php namespace App\ModelSerializers\Summit;
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
use App\Models\Foundation\Summit\Locations\Banners\SummitLocationBanner;
use ModelSerializers\SilverStripeSerializer;


/**
 * Class SummitLocationBannerSerializer
 * @package App\ModelSerializers\Summit
 */
class SummitLocationBannerSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [

        'Title'         => 'title:json_string',
        'Content'       => 'content:json_string',
        'Type'          => 'type:json_string',
        'Enabled'       => 'enabled:json_boolean',
        'LocationId'    => 'location_id:json_int',
        'ClassName'     => 'class_name:json_string',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        $values = parent::serialize($expand, $fields, $relations, $params);
        $banner  = $this->object;
        if(!$banner instanceof SummitLocationBanner) return [];
        return $values;
    }
}