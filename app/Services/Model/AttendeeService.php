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
use GuzzleHttp\Exception\ClientException;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IMemberRepository;
use models\summit\factories\SummitAttendeeFactory;
use models\summit\factories\SummitAttendeeTicketFactory;
use models\summit\ISummitAttendeeRepository;
use models\summit\ISummitAttendeeTicketRepository;
use models\summit\ISummitTicketTypeRepository;
use models\summit\Summit;
use models\summit\SummitAttendee;
use models\summit\SummitAttendeeTicket;
use services\apis\IEventbriteAPI;
/**
 * Class AttendeeService
 * @package App\Services\Model
 */
final class AttendeeService
    extends AbstractService
    implements IAttendeeService
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
     * @var ISummitTicketTypeRepository
     */
    private $ticket_type_repository;

    /**
     * @var ISummitAttendeeTicketRepository
     */
    private $ticket_repository;

    /**
     * @var IEventbriteAPI
     */
    private $eventbrite_api;


    public function __construct
    (
        ISummitAttendeeRepository $attendee_repository,
        IMemberRepository $member_repository,
        ISummitAttendeeTicketRepository $ticket_repository,
        ISummitTicketTypeRepository $ticket_type_repository,
        IEventbriteAPI $eventbrite_api,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->attendee_repository    = $attendee_repository;
        $this->ticket_repository      = $ticket_repository;
        $this->member_repository      = $member_repository;
        $this->ticket_type_repository = $ticket_type_repository;
        $this->eventbrite_api         = $eventbrite_api;
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

    /**
     * @param Summit $summit
     * @param int $attendee_id
     * @return void
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function deleteAttendee(Summit $summit, $attendee_id)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee_id){

            $attendee = $summit->getAttendeeById($attendee_id);
            if(is_null($attendee))
                throw new EntityNotFoundException();

            $this->attendee_repository->delete($attendee);
        });
    }

    /**
     * @param Summit $summit
     * @param int $attendee_id
     * @param array $data
     * @return SummitAttendee
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updateAttendee(Summit $summit, $attendee_id, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $attendee_id, $data){

            $attendee = $summit->getAttendeeById($attendee_id);
            if(is_null($attendee))
                throw new EntityNotFoundException(sprintf("attendee does not belongs to summit id %s", $summit->getId()));

            if(!isset($data['member_id']))
                throw new ValidationException("member_id is required");

            $member_id = intval($data['member_id']);
            $member    = $this->member_repository->getById($member_id);

            if(is_null($member))
                throw new EntityNotFoundException("member not found");

            // check if attendee already exist for this summit

            $old_attendee = $this->attendee_repository->getBySummitAndMember($summit, $member);
            if(!is_null($old_attendee) && $old_attendee->getId() != $attendee->getId())
                throw new ValidationException(sprintf("another attendee (%s) already exist for summit id %s and member id %s", $old_attendee->getId(), $summit->getId(), $member->getIdentifier()));

            return SummitAttendeeFactory::updateMainData($summit, $attendee, $member , $data);

        });
    }

    /**
     * @param SummitAttendee $attendee
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return SummitAttendeeTicket
     */
    public function addAttendeeTicket(SummitAttendee $attendee, array $data){
        return $this->tx_service->transaction(function() use($attendee, $data){

            if(!isset($data['ticket_type_id']))
                throw new ValidationException("ticket_type_id is mandatory!");

            $type = $this->ticket_type_repository->getById(intval($data['ticket_type_id']));

            if(is_null($type))
                throw new EntityNotFoundException(sprintf("ticket type %s not found!", $data['ticket_type_id']));

            $old_ticket = $this->ticket_repository->getByExternalOrderIdAndExternalAttendeeId
            (
                $data['external_order_id'],
                $data['external_attendee_id']
            );

            if(!is_null($old_ticket)) {
                if ($old_ticket->hasOwner())
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "external_order_id %s - external_attendee_id %s already assigned to attendee id %s",
                            $data['external_order_id'],
                            $data['external_attendee_id'],
                            $old_ticket->getOwner()->getId()
                        )
                    );
                $this->ticket_repository->delete($old_ticket);
            }

            // validate with external api ...

            try {
                $external_order          = $this->eventbrite_api->getOrder($data['external_order_id']);
                $external_attendee_found = false;
                $summit_external_id      = $external_order['event_id'];

                if (intval($attendee->getSummit()->getSummitExternalId()) !== intval($summit_external_id))
                    throw new ValidationException('order %s does not belongs to current summit!', $external_order_id);

                foreach ($external_order['attendees'] as $external_attendee){
                   if($data['external_attendee_id'] == $external_attendee['id']){
                       $external_attendee_found = true;
                       break;
                   }
                }
                if(!$external_attendee_found){
                    throw new ValidationException
                    (
                      sprintf("external_attendee_id %s does not belongs to external_order_id %s", $data['external_attendee_id'], $data['external_order_id'])
                    );
                }
            }
            catch (ClientException $ex1) {
                if ($ex1->getCode() === 400)
                    throw new EntityNotFoundException('external order does not exists!');
                if ($ex1->getCode() === 403)
                    throw new EntityNotFoundException('external order does not exists!');
                throw $ex1;
            }

            return SummitAttendeeTicketFactory::build($attendee, $type, $data);
        });
    }

    /**
     * @param SummitAttendee $attendee
     * @param int $ticket_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return SummitAttendeeTicket
     */
    public function deleteAttendeeTicket(SummitAttendee $attendee, $ticket_id)
    {
        return $this->tx_service->transaction(function() use($attendee, $ticket_id){
            $ticket = $attendee->getTicketById($ticket_id);
            if(is_null($ticket)){
                throw new EntityNotFoundException(sprintf("ticket id %s does not belongs to attendee id %s", $ticket_id, $attendee->getId()));
            }
            $attendee->removeTicket($ticket);
        });
    }
}