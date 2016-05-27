<?php namespace models\summit;
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

use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitEventType")
 * Class SummitEventType
 * @package models\summit
 */
class SummitEventType extends SilverstripeBaseModel
{
    use SummitOwned;

    protected static $array_mappings = array
    (
        'ID'            => 'id:json_int',
        'Type'          => 'name:json_string',
        'Color'         => 'color:json_string',
        'BlackoutTimes' => 'black_out_times:json_boolean',
    );

    public function toArray()
    {
        $values = parent::toArray();
        $color  = isset($values['color']) ? $values['color']:'';
        if(empty($color))
            $color = 'f0f0ee';
        if (strpos($color,'#') === false) {
            $color = '#'.$color;
        }
        $values['color'] = $color;
        return $values;
    }
}