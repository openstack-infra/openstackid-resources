<?php namespace ModelSerializers;
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
use models\summit\PresentationCategoryGroup;
/**
 * Class PresentationCategoryGroupSerializer
 * @package ModelSerializers
 */
class PresentationCategoryGroupSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Name'        => 'name:json_string',
        'Color'       => 'color:json_string',
        'Description' => 'description:json_string',
        'ClassName'   => 'class_name:json_string',
        'SummitId'    => 'summit_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [] )
    {
        $values = parent::serialize($expand, $fields, $relations, $params);
        $track_group = $this->object;
        if(!$track_group instanceof PresentationCategoryGroup) return $values;

        $color  = isset($values['color']) ? $values['color']:'';
        if(empty($color))
            $color = 'f0f0ee';
        if (strpos($color,'#') === false) {
            $color = '#'.$color;
        }
        $values['color'] = $color;

        $categories = [];

        foreach($track_group->getCategories() as $c)
        {
            if(!is_null($expand) &&  in_array('tracks', explode(',',$expand))){
                $categories[] = SerializerRegistry::getInstance()->getSerializer($c)->serialize();
            }
            else
                $categories[] = intval($c->getId());
        }

        $values['tracks'] = $categories;
        return $values;
    }
}