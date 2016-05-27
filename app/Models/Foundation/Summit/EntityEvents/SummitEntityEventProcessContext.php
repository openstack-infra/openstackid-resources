<?php namespace Models\foundation\summit\EntityEvents;
/**
 * Copyright 2016 OpenStack Foundation
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

use models\summit\SummitEntityEvent;
use ModelSerializers\SerializerRegistry;

/**
 * Class SummitEntityEventProcessContext
 * @package Models\foundation\summit\EntityEvents
 */
final class SummitEntityEventProcessContext
{
    private $delete_operations        = array();
    private $insert_operations        = array();
    private $update_operations        = array();
    private $summit_events_operations = array();
    /**
     * @var int
     */
    private $current_member_id;
    /**
     * @var EntityEventList
     */
    private $list;

    /**
     * SummitEntityEventProcessContext constructor.
     * @param int|null $current_member_id
     */
    public function __construct($current_member_id){
        $this->list              = new EntityEventList();
        $this->current_member_id = $current_member_id;
    }

    /**
     * @return int|null
     */
    public function getCurrentMemberId(){ return $this->current_member_id; }

    /**
     * @param SummitEntityEvent $entity_event
     * @return bool
     */
    public function existsOpType(SummitEntityEvent $entity_event){
        switch($entity_event->getType()){
            case 'INSERT': return $this->existsInsertOp($entity_event);break;
            case 'UPDATE': return $this->existsUpdateOp($entity_event);break;
            case 'DELETE': return $this->existsDeleteOp($entity_event);break;
            default: throw new \InvalidArgumentException;break;
        }
    }

    public function registerOpType(SummitEntityEvent $entity_event){
        switch($entity_event->getType()){
            case 'INSERT': return $this->registerInsertOp($entity_event);break;
            case 'UPDATE': return $this->registerUpdateOp($entity_event);break;
            case 'DELETE': return $this->registerDeleteOp($entity_event);break;
            default: throw new \InvalidArgumentException;break;
        }
    }
    /**
     * @param SummitEntityEvent $entity_event
     * @return bool
     */
    public function existsDeleteOp(SummitEntityEvent $entity_event){
        return isset($this->delete_operations[$entity_event->getKey()]);
    }

    /**
     * @param SummitEntityEvent $entity_event
     */
    public function registerDeleteOp(SummitEntityEvent $entity_event){
        $this->delete_operations[$entity_event->getKey()] = $entity_event->getKey();
    }

    /**
     * @param SummitEntityEvent $entity_event
     * @return bool
     */
    public function existsUpdateOp(SummitEntityEvent $entity_event){
        return isset($this->update_operations[$entity_event->getKey()]);
    }

    /**
     * @param SummitEntityEvent $entity_event
     */
    public function registerUpdateOp(SummitEntityEvent $entity_event){
        $this->update_operations[$entity_event->getKey()] = $entity_event->getKey();
    }

    /**
     * @param SummitEntityEvent $entity_event
     * @return bool
     */
    public function existsInsertOp(SummitEntityEvent $entity_event){
        return isset($this->insert_operations[$entity_event->getKey()]);
    }

    /**
     * @param SummitEntityEvent $entity_event
     */
    public function registerInsertOp(SummitEntityEvent $entity_event){
        $this->insert_operations[$entity_event->getKey()] = $entity_event->getKey();
    }

    /**
     * @param SummitEntityEvent $entity_event
     */
    public function registerSummitEventOp(SummitEntityEvent $entity_event){
        $key = $entity_event->getKey();
        if(!isset($this->summit_events_operations[$key])) $this->summit_events_operations[$key] = array();
        array_push($this->summit_events_operations[$key], ['idx' => $this->list->getIdx() - 1 , 'op' => $entity_event->getType()]);
    }

    /**
     * @param SummitEntityEvent $e
     */
    public function registerEntityEvent(SummitEntityEvent $e){
        $this->list[] = SerializerRegistry::getInstance()->getSerializer($e)->serialize
        (
            implode(',',['speakers','tracks','sponsors', 'floor'])
        );
    }

    /**
     * @return array
     */
    public function getListValues(){
        return $this->list->values();
    }

    public function postProcessList(){
        foreach ($this->summit_events_operations as $key => $ops) {
            $last_idx = null;
            $last_op = null;
            $must_insert = false;
            foreach ($ops as $op) {
                if (!is_null($last_idx))
                    unset($this->list[$last_idx]);
                $last_op = $op['op'];
                $last_idx = intval($op['idx']);
                $must_insert = !$must_insert && $last_op === 'INSERT' ? true : $must_insert;
            }
            $last_op = $must_insert && $last_op !== 'DELETE' ? 'INSERT' : $last_op;
            $summit_events_ops[$key] = array(['idx' => $last_idx, 'op' => ($last_op)]);
            // element update
            $e = $this->list[$last_idx];
            $e['type'] = $last_op;
            $this->list[$last_idx] = $e;
        }
    }

    /**
     * @return int
     */
    public function getListSize(){
        return $this->list->size();
    }
}