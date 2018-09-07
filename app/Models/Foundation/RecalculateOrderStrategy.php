<?php namespace App\Models\Foundation;
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
use App\Models\IRecalculateOrderStrategy;
use models\exceptions\ValidationException;
/**
 * Class RecalculateOrderStrategy
 * @package App\Models\Foundation
 */
final class RecalculateOrderStrategy implements IRecalculateOrderStrategy
{
    /**
     * @param array $collection
     * @param IOrderableEntity $entity
     * @param int $new_order
     * @throws ValidationException
     */
    public function recalculateOrder(array $collection , IOrderableEntity $entity, $new_order){

        $former_order = $entity->getOrder();

        $collection = array_slice($collection,0, count($collection), false);
        $max_order  = count($collection);

        if($new_order > $max_order)
            throw new ValidationException(sprintf("max order is %s", $max_order));

        unset($collection[$former_order - 1]);

        $collection = array_merge
        (
            array_slice($collection, 0, $new_order-1 , true) ,
            [$entity] ,
            array_slice($collection, $new_order -1 , count($collection), true)
        );

        $order = 1;
        foreach($collection as $e){
            $e->setOrder($order);
            $order++;
        }
    }
}