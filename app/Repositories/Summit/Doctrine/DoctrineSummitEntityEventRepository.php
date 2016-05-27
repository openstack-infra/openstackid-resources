<?php namespace repositories\summit;
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
use DateTime;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use models\summit\ISummitEntityEventRepository;
use models\summit\Summit;
use models\summit\SummitEntityEvent;
use repositories\SilverStripeDoctrineRepository;

/**
 * Class DoctrineSummitEntityEventRepository
 * @package repositories\summit
 */
final class DoctrineSummitEntityEventRepository
    extends SilverStripeDoctrineRepository
    implements ISummitEntityEventRepository
{

    /**
     * @param Summit $summit
     * @param int|null $member_id
     * @param int|null $from_id
     * @param DateTime|null $from_date
     * @param int $limit
     * @param bool $detach
     * @return SummitEntityEvent[]
     */
    public function getEntityEvents
    (
        Summit $summit,
        $member_id = null,
        $from_id   = null,
        DateTime $from_date = null,
        $limit = 25,
        $detach = true
    )
    {
        $filters = '';
        if(!is_null($from_id))
        {
            $filters .= " AND SummitEntityEvent.ID > {$from_id} ";
        }

        if(!is_null($from_date))
        {
            $str_date = $from_date->format("Y-m-d H:i:s");
            // CDT TO UTC
            $filters .= " AND DATE_ADD(SummitEntityEvent.Created,INTERVAL + 5 HOUR) >= '{$str_date}' ";
        }

        $query = <<<SQL
SELECT * FROM
(
	SELECT * FROM SummitEntityEvent
	WHERE
	(
		(EntityClassName <> 'MySchedule' AND EntityClassName <> 'SummitAttendee')
		-- GLOBAL TRUNCATE
		OR (EntityClassName = 'WipeData' AND EntityID = 0)
	)
	AND SummitID = {$summit->getId()}
	{$filters}
	LIMIT {$limit}
)
AS GLOBAL_EVENTS
SQL;

        if(!is_null($member_id)){
            $query .= <<<SQL
 UNION
SELECT * FROM
(
	SELECT * FROM SummitEntityEvent
	WHERE
	(
		EntityClassName = 'MySchedule'
		AND OwnerID = {$member_id}
	)
	AND SummitID = {$summit->getId()}
	{$filters}
	LIMIT {$limit}
)
AS MY_SCHEDULE
UNION
SELECT * FROM
(
	SELECT * FROM SummitEntityEvent
	WHERE
	(
		EntityClassName = 'WipeData' AND EntityID = {$member_id}
	)
	AND SummitID = {$summit->getId()}
	{$filters}
	LIMIT {$limit}
) AS USER_WIPE_DATA
SQL;
        }

        $query .= <<<SQL
 ORDER BY Created ASC LIMIT {$limit};
SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(\models\summit\SummitEntityEvent::class, 'e');
        // build rsm here
        $native_query = $this->_em->createNativeQuery($query, $rsm);

        $entity_events = $native_query->getResult();

        if($detach) $this->_em ->clear(\models\summit\SummitEntityEvent::class);

        return $entity_events;
    }

    /**
     * @param Summit $summit
     * @return int
     */
    public function getLastEntityEventId(Summit $summit)
    {
        $query = <<<SQL
SELECT ID FROM SummitEntityEvent WHERE SummitID = {$summit->getId()} ORDER BY ID DESC LIMIT 1;
SQL;

        return intval($this->_em->getConnection()->executeQuery($query)->fetchColumn(0));
    }
}