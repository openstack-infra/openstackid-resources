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

use models\utils\IEntity;
use models\utils\BaseModelEloquent;
/**
 * Class Summit
 * @package models\summit
 */
class Summit extends BaseModelEloquent implements IEntity
{
    protected $table = 'Summit';

    protected $connection = 'ss';

    protected $array_mappings = array
    (
        'ID'   => 'id',
        'Name' => 'name',
    );

    protected $hidden = array
    (

    );

    /**
     * @return int
     */
    public function getIdentifier()
    {
        return (int)$this->ID;
    }

    /**
     * @return SummitAbstractLocation[]
     */
    public function locations()
    {
        $res = $this->hasMany('models\summit\SummitAbstractLocation', 'SummitID', 'ID')->get();
        $locations = array();
        foreach($res as $l)
        {
            $class = 'models\\summit\\'.$l->ClassName;
            $entity = $class::find($l->ID);
            array_push($locations, $entity);
        }
        return $locations;
    }

    public function event_types()
    {
        $this->hasMany('models\summit\SummitEventType', 'SummitID', 'ID')->get();
    }

    public function summit_types()
    {
        $this->hasMany('models\summit\SummitType', 'SummitID', 'ID')->get();
    }

    public function schedule()
    {
        return $this->hasMany('models\summit\SummitEvent', 'SummitID', 'ID')->where('Published','=','1')->get();
    }
}