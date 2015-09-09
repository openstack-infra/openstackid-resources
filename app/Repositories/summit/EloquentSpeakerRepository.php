<?php
/**
 * Copyright 2015 OpenStack Foundation
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

use models\summit\PresentationSpeaker;
use models\summit\ISpeakerRepository;
use models\summit\Summit;
use models\utils\EloquentBaseRepository;
use utils\Filter;
use utils\Order;
use utils\PagingInfo;
use utils\PagingResponse;
use utils\Sort;
use DB;
/**
 * Class EloquentSpeakerRepository
 * @package repositories\summit
 */
final class EloquentSpeakerRepository extends EloquentBaseRepository implements ISpeakerRepository
{

    /**
     * @param PresentationSpeaker $speaker
     */
    public function __construct(PresentationSpeaker $speaker)
    {
        $this->entity = $speaker;
    }

    /**
     * @param Summit $summit
     * @param PagingInfo $paging_info
     * @param Filter|null $filter
     * @param Order|null $order
     * @return PagingResponse
     */
    public function getSpeakersBySummit(Summit $summit, PagingInfo $paging_info, Filter $filter = null, Order $order = null)
    {

        $extra_filters = '';
        $extra_orders  = '';

        $bindings      = array
        (
            'summit_id'  => $summit->getIdentifier(),
        );

        if(!is_null($filter))
        {
            $extra_filters = ' AND '.$filter->toRawSQL(array
                (
                    'first_name' => 'M.FirstName',
                    'last_name'  => 'M.Surname',
                    'email'      => 'M.Email',
                ));
            $bindings = array_merge($bindings, $filter->getSQLBindings());
        }

        if(!is_null($order))
        {
            $extra_orders = $order->toRawSQL(array
            (
                    'first_name' => 'M.FirstName',
                    'last_name'  => 'M.Surname',
            ));
        }

        $total     = DB::connection('ss')->select("
SELECT COUNT(S.ID) AS QTY From PresentationSpeaker S
LEFT JOIN Member M ON M.ID = S.MemberID
WHERE EXISTS
(
    SELECT E.ID FROM SummitEvent E
    INNER JOIN Presentation P ON E.ID = P.ID
    INNER JOIN Presentation_Speakers PS ON PS.PresentationID = P.ID
    WHERE E.SummitID = :summit_id AND E.Published = 1 AND PS.PresentationSpeakerID = S.ID
) {$extra_filters};", $bindings);
        $total     = intval($total[0]->QTY);

        $bindings = array_merge( $bindings, array
        (
            'per_page'  => $paging_info->getPerPage(),
            'offset'    => $paging_info->getOffset(),
        ));
        $rows      = DB::connection('ss')->select("
SELECT S.* From PresentationSpeaker S
LEFT JOIN Member M ON M.ID = S.MemberID
WHERE EXISTS
(
    SELECT E.ID FROM SummitEvent E
    INNER JOIN Presentation P ON E.ID = P.ID
    INNER JOIN Presentation_Speakers PS ON PS.PresentationID = P.ID
    WHERE E.SummitID = :summit_id AND E.Published = 1 AND PS.PresentationSpeakerID = S.ID
) {$extra_filters} {$extra_orders} limit :per_page offset :offset;", $bindings);

        $items = array();
        foreach($rows as $row)
        {
            $instance = new PresentationSpeaker();
            $instance->setRawAttributes((array)$row, true);
            array_push($items, $instance);
        }

        $last_page = (int) ceil($total / $paging_info->getPerPage());

        return new PagingResponse($total, $paging_info->getPerPage(), $paging_info->getCurrentPage(), $last_page, $items);
    }
}