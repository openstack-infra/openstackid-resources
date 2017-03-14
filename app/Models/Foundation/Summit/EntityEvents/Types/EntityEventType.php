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

use Illuminate\Support\Facades\Log;
use models\summit\SummitEntityEvent;
use models\utils\IEntity;
use LaravelDoctrine\ORM\Facades\Registry;
use Doctrine\ORM\EntityManager;

/**
 * Class EntityEventType
 * @package Models\foundation\summit\EntityEvents
 */
abstract class EntityEventType implements IEntityEventType
{
    const EntityManager = 'ss';

    /**
     * @var SummitEntityEvent
     */
    protected $entity_event;

    /**
     * @var SummitEntityEventProcessContext
     */
    protected $process_ctx;

    /**
     * SummitEntityEventType constructor.
     * @param SummitEntityEvent $entity_event
     * @param SummitEntityEventProcessContext $process_ctx
     */
    public function __construct(SummitEntityEvent $entity_event, SummitEntityEventProcessContext $process_ctx)
    {
        $this->entity_event = $entity_event;
        $this->process_ctx  = $process_ctx;
    }

    /**
     * @return IEntity|null
     */
    abstract protected function registerEntity();

    /**
     * @return EntityManager
     */
    protected function getEM(){
        return Registry::getManager(self::EntityManager);
    }

    protected function getLocalClassName(){
        $class_name =  $this->entity_event->getEntityClassName();
        switch ($class_name){
            case 'MySchedule':
            case 'MyFavorite':
                return 'models\summit\SummitEvent';
            break;
            case 'PresentationType':
                return 'models\summit\SummitEventType';
            break;
        }
        return sprintf('models\summit\%s',$class_name);
    }

    protected function evictEntity(){
        $cache      = $this->getEM()->getCache();
        $class_name = $this->getLocalClassName();

        if(!is_null($cache) && !empty($class_name) && $cache->containsEntity($class_name, $this->entity_event->getEntityId())) {
            $cache->evictEntity($class_name, $this->entity_event->getEntityId());
            Log::debug(sprintf("class_name % - id %s evicted from 2nd level cache", $class_name, $this->entity_event->getEntityId()));
        }
    }
}