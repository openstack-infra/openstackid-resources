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
use App\Models\Foundation\Summit\Factories\SummitSelectionPlanFactory;
use App\Models\Foundation\Summit\SelectionPlan;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
/**
 * Class SummitSelectionPlanService
 * @package App\Services\Model
 */
final class SummitSelectionPlanService
    extends AbstractService
    implements ISummitSelectionPlanService
{

    /**
     * @param Summit $summit
     * @param array $payload
     * @return SelectionPlan
     * @throws ValidationException
     */
    public function addSelectionPlan(Summit $summit, array $payload)
    {
        return $this->tx_service->transaction(function() use($summit, $payload){

            $selection_plan = SummitSelectionPlanFactory::build($payload, $summit);

            $former_selection_plan = $summit->getSelectionPlanByName($selection_plan->getName());

            if(!is_null($former_selection_plan)){
                throw new ValidationException(trans(
                    'validation_errors.SummitSelectionPlanService.addSelectionPlan.alreadyExistName',
                    [
                        'summit_id' => $summit->getId()
                    ]
                ));
            }

            // validate selection plan
            $summit->checkSelectionPlanConflicts($selection_plan);

            $summit->addSelectionPlan($selection_plan);

            return $selection_plan;
        });
    }

    /**
     * @param Summit $summit
     * @param int $selection_plan_id
     * @param array $payload
     * @return SelectionPlan
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updateSelectionPlan(Summit $summit, $selection_plan_id, array $payload)
    {
        return $this->tx_service->transaction(function() use($summit, $selection_plan_id, $payload){

            $selection_plan = $summit->getSelectionPlanById($selection_plan_id);
            if(is_null($selection_plan))
                throw new EntityNotFoundException(trans
                ('not_found_errors.SummitSelectionPlanService.updateSelectionPlan.SelectionPlanNotFound',
                    [
                        'selection_plan_id' => $selection_plan_id,
                        'summit_id' => $summit->getId()
                    ]
                ));

            if(isset($payload['name'])) {
                $former_selection_plan = $summit->getSelectionPlanByName($payload['name']);
                if ($former_selection_plan->getId() != $selection_plan_id && !is_null($former_selection_plan)) {
                    throw new ValidationException(trans(
                        'validation_errors.SummitSelectionPlanService.updateSelectionPlan.alreadyExistName',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    ));
                }
            }

            SummitSelectionPlanFactory::populate($selection_plan, $payload, $summit);

            // validate selection plan
            $summit->checkSelectionPlanConflicts($selection_plan);

            return $selection_plan;
        });
    }

    /**
     * @param Summit $summit
     * @param int $selection_plan_id
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSelectionPlan(Summit $summit, $selection_plan_id)
    {
        return $this->tx_service->transaction(function() use($summit, $selection_plan_id){

            $selection_plan = $summit->getSelectionPlanById($selection_plan_id);
            if(is_null($selection_plan))
                throw new EntityNotFoundException(trans
                ('not_found_errors.SummitSelectionPlanService.deleteSelectionPlan.SelectionPlanNotFound',
                    [
                        'selection_plan_id' => $selection_plan_id,
                        'summit_id' => $summit->getId()
                    ]
                ));

            $summit->removeSelectionSelectionPlan($selection_plan);
        });
    }
}