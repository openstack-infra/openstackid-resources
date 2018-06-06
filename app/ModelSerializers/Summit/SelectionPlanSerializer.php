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
use App\Models\Foundation\Summit\SelectionPlan;
use ModelSerializers\SerializerRegistry;
use ModelSerializers\SilverStripeSerializer;
/**
 * Class SelectionPlanSerializer
 * @package App\ModelSerializers\Summit
 */
final class SelectionPlanSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'Name'                  => 'name:json_string',
        'Enabled'               => 'is_enabled:json_boolean',
        'SubmissionBeginDate'   => 'submission_begin_date:datetime_epoch',
        'SubmissionEndDate'     => 'submission_end_date:datetime_epoch',
        'VotingBeginDate'       => 'voting_begin_date:datetime_epoch',
        'VotingEndDate'         => 'voting_end_date:datetime_epoch',
        'SelectionBeginDate'    => 'selection_begin_date:datetime_epoch',
        'SelectionEndDate'      => 'selection_end_date:datetime_epoch',
        'SummitId'              => 'summit_id:json_int',
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
        $selection_plan = $this->object;
        if (!$selection_plan instanceof SelectionPlan) return [];
        $values = parent::serialize($expand, $fields, $relations, $params);

        $category_groups  = [];
        foreach ($selection_plan->getCategoryGroups() as $group) {
            $category_groups[] = $group->getId();
        }
        $values['category_groups'] = $category_groups;

        if (!empty($expand)) {
            $expand = explode(',', $expand);
            foreach ($expand as $relation) {
                switch (trim($relation)) {
                    case 'category_groups':{
                        $category_groups  = [];
                        foreach ($selection_plan->getCategoryGroups() as $group) {
                            $category_groups[] = SerializerRegistry::getInstance()->getSerializer($group)->serialize($expand);
                        }
                        $values['category_groups'] = $category_groups;
                    }
                    break;
                }
            }
        }

        return $values;
    }
}