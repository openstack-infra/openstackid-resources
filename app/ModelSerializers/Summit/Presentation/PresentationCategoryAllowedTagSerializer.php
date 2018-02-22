<?php namespace App\ModelSerializers\Summit\Presentation;
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
use App\Models\Foundation\Summit\Events\Presentations\PresentationCategoryAllowedTag;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class PresentationCategoryAllowedTagSerializer
 * @package App\ModelSerializers\Summit\Presentation
 */
final class PresentationCategoryAllowedTagSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Default' => 'is_default:json_boolean',
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
        $allowed_tag =  $this->object;
        if(!$allowed_tag instanceof PresentationCategoryAllowedTag) return [];
        $values['tag'] = $allowed_tag->getTag()->getTag();
        $values['id']  = $allowed_tag->getTag()->getId();
        return $values;
    }
}