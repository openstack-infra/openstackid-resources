<?php namespace ModelSerializers;
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

use models\summit\PrivatePresentationCategoryGroup;

/**
 * Class PrivatePresentationCategoryGroupSerializer
 * @package ModelSerializers
 */
final class PrivatePresentationCategoryGroupSerializer
    extends PresentationCategoryGroupSerializer
{
    protected static $array_mappings = [
        'SubmissionBeginDate'         => 'submission_begin_date:datetime_epoch',
        'SubmissionEndDate'           => 'submission_end_date:datetime_epoch',
        'MaxSubmissionAllowedPerUser' => 'max_submission_allowed_per_user:json_int',
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

        $track_group  = $this->object;
        if(!$track_group instanceof PrivatePresentationCategoryGroup) return $values;

        $allowed_groups= [];

        foreach($track_group->getAllowedGroups() as $g)
        {
            if(!is_null($expand) &&  in_array('allowed_groups', explode(',',$expand))){
                $allowed_groups[] = SerializerRegistry::getInstance()->getSerializer($g)->serialize();
            }
            else
                $allowed_groups[] = intval($g->getId());
        }

        $values['allowed_groups'] = $allowed_groups;
        return $values;
    }
}