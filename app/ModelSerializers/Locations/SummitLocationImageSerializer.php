<?php namespace ModelSerializers\Locations;
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
use Illuminate\Support\Facades\Config;
use ModelSerializers\SilverStripeSerializer;

/**
 * Class SummitLocationImageSerializer
 * @package ModelSerializers\Locations
 */
class SummitLocationImageSerializer  extends SilverStripeSerializer
{
    protected static $array_mappings = array
    (
        'Name'         => 'name:json_text',
        'Description'  => 'description:json_text',
        'ClassName'    => 'class_name:json_text',
        'LocationId'   => 'location_id:json_int',
        'Order'        => 'order:json_int',
    );

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

        if($this->object->hasPicture())
        {
            $picture             = $this->object->getPicture();
            $values['image_url'] = $picture->getFilename()->getUrl();
        }
        else
        {
            $values['image_url'] = null;
        }
        return $values;
    }
}