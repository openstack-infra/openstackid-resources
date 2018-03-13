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
use App\Models\Foundation\Summit\Events\RSVP\RSVPTemplate;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class RSVPTemplateSerializer
 * @package App\ModelSerializers\Summit
 */
final class RSVPTemplateSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [

        'Title'        => 'title:json_string',
        'Enabled'      => 'is_enable:json_boolean',
        'CreatedById'  => 'created_by_id:json_int',
        'SummitId'     => 'summit_id:json_int',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [])
    {
        $template = $this->object;
        if(! $template instanceof RSVPTemplate) return [];
        $values  = parent::serialize($expand, $fields, $relations, $params);

        $questions           = [];
        foreach ($template->getQuestions() as $question){
            $questions[] = SerializerRegistry::getInstance()->getSerializer($question)->serialize($expand, [], ['none']);
        }

        $values['questions'] = $questions;

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {

                    case 'created_by':
                    {
                        if($template->hasCreatedBy()) {
                            unset($values['created_by_id']);
                            $values['created_by'] = SerializerRegistry::getInstance()->getSerializer($template)->serialize($expand, [], ['none']);
                        }
                    }
                    break;
                }
            }
        }

        return $values;
    }

}