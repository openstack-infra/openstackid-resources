<?php namespace App\Models\Foundation\Main;
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
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use models\exceptions\ValidationException;
/**
 * Trait OrderableChilds
 * @package App\Models\Foundation\Main
 */
trait OrderableChilds
{
    /**
     * @param Selectable $collection
     * @param IOrderable $element
     * @param $new_order
     * @throws ValidationException
     */
    private static function recalculateOrderFor(Selectable $collection, IOrderable $element, $new_order){
        $criteria     = Criteria::create();
        $criteria->orderBy(['order'=> 'ASC']);

        $elements     = $collection->matching($criteria)->toArray();
        $elements     = array_slice($elements,0, count($elements), false);
        $max_order    = count($elements);
        $former_order = 1;

        foreach ($elements as $e){
            if($e->getId() == $element->getId()) break;
            $former_order++;
        }

        if($new_order > $max_order)
            throw new ValidationException(sprintf("max order is %s", $max_order));

        unset($elements[$former_order - 1]);

        $elements = array_merge
        (
            array_slice($elements, 0, $new_order -1 , true) ,
            [$element] ,
            array_slice($elements, $new_order -1 , count($elements), true)
        );

        $order = 1;
        foreach($elements as $e){
            $e->setOrder($order);
            $order++;
        }
    }
}