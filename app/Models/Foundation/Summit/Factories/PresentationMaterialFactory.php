<?php namespace App\Models\Foundation\Summit\Factories;
/**
 * Copyright 2019 OpenStack Foundation
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
use models\summit\PresentationMaterial;
/**
 * Class PresentationMaterialFactory
 * @package App\Models\Foundation\Summit\Factories
 */
abstract class PresentationMaterialFactory
{
    /**
     * @param PresentationMaterial $presentationMaterial
     * @param array $data
     * @return PresentationMaterial
     */
    public static function populate(PresentationMaterial $presentationMaterial, array $data){

        if(isset($data['name']))
            $presentationMaterial->setName(trim($data['name']));

        if(isset($data['description']))
            $presentationMaterial->setDescription(trim($data['description']));

        if(isset($data['display_on_site']))
            $presentationMaterial->setDisplayOnSite(isset($data['display_on_site']) ? (bool)$data['display_on_site'] : true);

        return $presentationMaterial;
    }
}