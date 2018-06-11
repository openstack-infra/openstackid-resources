<?php namespace App\Services\Model;
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
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
/**
 * Interface ISummitSelectionPlanService
 * @package App\Services\Model
 */
interface ISummitSelectionPlanService
{
    /**
     * @param Summit $summit
     * @param array $payload
     * @return SelectionPlan
     * @throws ValidationException
     */
    public function addSelectionPlan(Summit $summit, array $payload);

    /**
     * @param Summit $summit
     * @param int $selection_plan_id
     * @param array $payload
     * @return SelectionPlan
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updateSelectionPlan(Summit $summit, $selection_plan_id, array $payload);

    /**
     * @param Summit $summit
     * @param int $selection_plan_id
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSelectionPlan(Summit $summit, $selection_plan_id);

    /**
     * @param Summit $summit
     * @param int $selection_plan_id
     * @param int $track_group_id
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return void
     */
    public function addTrackGroupToSelectionPlan(Summit $summit, $selection_plan_id, $track_group_id);

    /**
     * @param Summit $summit
     * @param int $selection_plan_id
     * @param int $track_group_id
     * @throws EntityNotFoundException
     * @throws ValidationException
     * @return void
     */
    public function deleteTrackGroupToSelectionPlan(Summit $summit, $selection_plan_id, $track_group_id);
}