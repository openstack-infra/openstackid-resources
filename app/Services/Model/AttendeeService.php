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
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\summit\factories\SummitAttendeeFactory;
use models\summit\ISummitAttendeeRepository;
use models\summit\Summit;
use models\summit\SummitAttendee;
/**
 * Class AttendeeService
 * @package App\Services\Model
 */
class AttendeeService implements IAttendeeService
{

    /**
     * @var ISummitAttendeeRepository
     */
    private $attendee_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    public function __construct
    (
        ISummitAttendeeRepository $attendee_repository,
        IMemberRepository $member_repository,
        ITransactionService $tx_service
    )
    {
        $this->attendee_repository = $attendee_repository;
        $this->member_repository   = $member_repository;
        $this->tx_service          = $tx_service;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitAttendee
     */
    public function addAttendee(Summit $summit, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $data){

            if(!isset($data['member_id']))
                throw new ValidationException("member_id is required");

            $member_id = intval($data['member_id']);
            $member    = $this->member_repository->getById($member_id);

            if(is_null($member))
                throw new EntityNotFoundException("member not found");

            // check if attendee already exist for this summit

            $old_attendee = $this->attendee_repository->getBySummitAndMember($summit, $member);
            if(!is_null($old_attendee))
                throw new ValidationException(sprintf("attendee already exist for summit id %s and member id %s", $summit->getId(), $member->getIdentifier()));

            $attendee = SummitAttendeeFactory::build($summit, $member, $data);

            $this->attendee_repository->add($attendee);

            return $attendee;
        });
    }
}