<?php
/**
 * Copyright 20`5 OpenStack Foundation
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

namespace repositories\summit;

use DB;
use models\summit\ISummitEventRepository;
use models\summit\SummitEvent;
use models\utils\EloquentBaseRepository;
use utils\ExistsFilterManyManyMapping;
use utils\ExistsFilterManyToOneMapping;
use utils\Filter;
use utils\Order;

/**
 * Class EloquentSummitEventRepository
 * @package repositories\summit
 */
final class EloquentSummitEventRepository extends EloquentBaseRepository implements ISummitEventRepository
{
    /**
     * @param SummitEvent $event
     */
    public function __construct(SummitEvent $event)
    {
        $this->entity = $event;
    }

    /**
     * @param SummitEvent $event
     * @return SummitEvent[]
     */
    public function getPublishedOnSameTimeFrame(SummitEvent $event)
    {
        $summit     = $event->getSummit();
        $end_date   = $event->EndDateUTC;
        $start_date = $event->StartDateUTC;
        return $this->entity
            ->where('SummitID', '=', $summit->getIdentifier())
            ->where('Published', '=', 1)
            ->where('StartDate', '<=', $end_date->format('Y-m-d H:i:s'))
            ->where('EndDate',   '>=', $start_date->format('Y-m-d H:i:s'))
            ->get();
    }

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter $filter
     * @return array
     */
    public function getAllByPage($page, $per_page, Filter $filter)
    {
        return $this->_getAllByPage($page, $per_page, $filter);
    }

    /**
     * @param int $page
     * @param int $per_page
     * @param Filter $filter
     * @return array
     */
    public function getAllPublishedByPage($page, $per_page, Filter $filter)
    {
        return $this->_getAllByPage($page, $per_page, $filter, true);
    }

    /**
     * @param $page
     * @param $per_page
     * @param Filter $filter
     * @param bool|false $published
     * @return array
     */
    private function _getAllByPage($page, $per_page, Filter $filter, $published = false)
    {
        $rel = $this->entity->newQuery();

        if($published)
        {
            $rel = $rel->where('Published','=','1');
        }

        if(!is_null($filter))
        {
            $filter->apply2Relation($rel, array
            (
                'title'         => 'SummitEvent.Title',
                'summit_id'     => 'SummitEvent.SummitID',
                'start_date'    => 'SummitEvent.StartDate:datetime_epoch',
                'end_date'      => 'SummitEvent.EndDate:datetime_epoch',
                'tags'          => new ExistsFilterManyManyMapping
                (
                    'Tag',
                    'SummitEvent_Tags',
                    'SummitEvent_Tags.TagID = Tag.ID',
                    "SummitEvent_Tags.SummitEventID = SummitEvent.ID AND Tag.Tag :operator ':value'"
                ),
                'summit_type_id'=> new ExistsFilterManyManyMapping
                (
                    'SummitType',
                    'SummitEvent_AllowedSummitTypes',
                    'SummitType.ID = SummitEvent_AllowedSummitTypes.SummitTypeID',
                    'SummitEvent_AllowedSummitTypes.SummitEventID = SummitEvent.ID AND SummitType.ID :operator :value'
                ),
                'event_type_id' => new ExistsFilterManyToOneMapping
                (
                    'SummitEventType',
                    'SummitEventType.ID = SummitEvent.TypeID AND SummitEventType.ID :operator :value'
                ),
            ));
        }

        $rel = $rel->orderBy('StartDate','asc')->orderBy('EndDate','asc');

        $pagination_result = $rel->paginate($per_page);
        $total             = $pagination_result->total();
        $items             = $pagination_result->items();
        $per_page          = $pagination_result->perPage();
        $current_page      = $pagination_result->currentPage();
        $last_page         = $pagination_result->lastPage();
        $events = array();
        foreach($items as $e)
        {
            $class = 'models\\summit\\'.$e->ClassName;
            $entity = $class::find($e->ID);
            array_push($events, $entity);
        }
        return array($total,$per_page, $current_page, $last_page, $events);
    }

}