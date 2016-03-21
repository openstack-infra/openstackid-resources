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

/**
 * Class SummitGeoLocatedLocation
 * @package models\summit
 */
class SummitGeoLocatedLocation extends SummitAbstractLocation
{
    protected $mtiClassType = 'concrete';

    protected $array_mappings = array
    (
        'ID'           => 'id:json_int',
        'Name'         => 'name:json_string',
        'Description'  => 'description:json_string',
        'ClassName'    => 'class_name',
        'LocationType' => 'location_type',
        'Address1'     => 'address_1:json_string',
        'Address2'     => 'address_2:json_string',
        'ZipCode'      => 'zip_code',
        'City'         => 'city:json_string',
        'State'        => 'state:json_string',
        'Country'      => 'country:json_string',
        'Lng'          => 'lng',
        'Lat'          => 'lat',
    );

    /**
     * @return SummitLocationMap[]
     */
    public function maps()
    {
        return $this->hasMany('models\summit\SummitLocationImage', 'LocationID', 'ID')
                    ->where('ClassName','=', 'SummitLocationMap')
                    ->orderBy('Order','ASC')->get();
    }

    /**
     * @return SummitLocationImage[]
     */
    public function images()
    {
        return $this->hasMany('models\summit\SummitLocationImage', 'LocationID', 'ID')
            ->where('ClassName','=', 'SummitLocationImage')
            ->orderBy('Order','ASC')->get();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $values = parent::toArray();

        $maps   = array();
        foreach($this->maps() as $m)
        {
            array_push($maps, $m->toArray());
        }
        $values['maps'] = $maps;


        $images   = array();
        foreach($this->images() as $i)
        {
            array_push($images, $i->toArray());
        }
        $values['images'] = $images;

        return $values;
    }

}