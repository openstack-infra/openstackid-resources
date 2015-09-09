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

use models\utils\SilverstripeBaseModel;


/**
 * Class PresentationCategoryGroup
 * @package models\summit
 */
class PresentationCategoryGroup extends SilverstripeBaseModel
{
    protected $table = 'PresentationCategoryGroup';

    protected $array_mappings = array
    (
        'ID'          => 'id:json_int',
        'Name'        => 'name:json_string',
        'Color'       => 'color:json_string',
        'Description' => 'description:json_string',
    );


    /**
     * @return PresentationCategory[]
     */
    public function categories()
    {
        return $this->belongsToMany('models\summit\PresentationCategory','PresentationCategoryGroup_Categories','PresentationCategoryGroupID','PresentationCategoryID')->get();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $values = parent::toArray();
        $categories = array();
        foreach($this->categories() as $c)
        {
            array_push($categories, $c->ID);
        }
        $values['categories'] = $categories;
        return $values;
    }
}