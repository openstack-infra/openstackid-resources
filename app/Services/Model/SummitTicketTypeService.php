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
use App\Events\SummitTicketTypeInserted;
use App\Events\SummitTicketTypeDeleted;
use App\Events\SummitTicketTypeUpdated;
use App\Models\Foundation\Summit\Factories\SummitTicketTypeFactory;
use Illuminate\Support\Facades\Event;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\ISummitTicketTypeRepository;
use models\summit\Summit;
use models\summit\SummitTicketType;
use services\apis\IEventbriteAPI;
/**
 * Class SummitTicketTypeService
 * @package App\Services\Model
 */
final class SummitTicketTypeService
    extends AbstractService
    implements ISummitTicketTypeService
{

    /**
     * @var ISummitTicketTypeRepository
     */
    private $repository;

    /**
     * @var IEventbriteAPI
     */
    private $eventbrite_api;

    /**
     * SummitTicketTypeService constructor.
     * @param ISummitTicketTypeRepository $repository
     * @param IEventbriteAPI $eventbrite_api
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitTicketTypeRepository $repository,
        IEventbriteAPI $eventbrite_api,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->repository     = $repository;
        $this->eventbrite_api = $eventbrite_api;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitTicketType
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addTicketType(Summit $summit, array $data)
    {
        $ticket_type =  $this->tx_service->transaction(function() use ($summit, $data){

            $former_ticket_type = $summit->getTicketTypeByName(trim($data['name']));

            if(!is_null($former_ticket_type)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.SummitTicketTypeService.addTicketType.NameAlreadyExists'
                    ),
                    [
                        'name' => trim($data['name']),
                        'summit_id' => $summit->getId()
                    ]
                );
            }

            $former_ticket_type = $summit->getTicketTypeByExternalId(trim($data['external_id']));
            if(!is_null($former_ticket_type)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.SummitTicketTypeService.addTicketType.ExternalIdAlreadyExists'
                    ),
                    [
                        'external_id' => trim($data['external_id']),
                        'summit_id'   => $summit->getId()
                    ]
                );
            }

            $ticket_type = SummitTicketTypeFactory::build($data);

            $summit->addTicketType($ticket_type);
            return $ticket_type;
        });

        Event::fire
        (
            new SummitTicketTypeInserted
            (
                $ticket_type->getId(),
                $ticket_type->getSummitId()
            )
        );

        return $ticket_type;

    }

    /**
     * @param Summit $summit
     * @param int $ticket_type_id
     * @param array $data
     * @return SummitTicketType
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateTicketType(Summit $summit, $ticket_type_id, array $data)
    {
        return $this->tx_service->transaction(function() use ($summit, $ticket_type_id, $data){

            if(isset($data['name'])) {
                $former_ticket_type = $summit->getTicketTypeByName(trim($data['name']));

                if (!is_null($former_ticket_type) && $former_ticket_type->getId() != $ticket_type_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.SummitTicketTypeService.updateTicketType.NameAlreadyExists'
                        ),
                        [
                            'name' => trim($data['name']),
                            'summit_id' => $summit->getId()
                        ]
                    );
                }
            }

            if(isset($data['external_id'])) {
                $former_ticket_type = $summit->getTicketTypeByExternalId(trim($data['external_id']));
                if (!is_null($former_ticket_type) && $former_ticket_type->getId() != $ticket_type_id) {
                    throw new ValidationException
                    (
                        trans
                        (
                            'validation_errors.SummitTicketTypeService.updateTicketType.ExternalIdAlreadyExists'
                        ),
                        [
                            'external_id' => trim($data['external_id']),
                            'summit_id' => $summit->getId()
                        ]
                    );
                }
            }

            $ticket_type = $summit->getTicketTypeById($ticket_type_id);

            if(is_null($ticket_type)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.SummitTicketTypeService.updateTicketType.TicketTypeNotFound',
                        [
                            'ticket_type_id' => $ticket_type_id,
                            'summit_id'      => $summit->getId()
                        ]
                    )
                );
            }

            $ticket_type = SummitTicketTypeFactory::populate($ticket_type, $data);

            Event::fire
            (
                new SummitTicketTypeUpdated
                (
                    $ticket_type->getId(),
                    $ticket_type->getSummitId()
                )
            );

            return $ticket_type;
        });
    }

    /**
     * @param Summit $summit
     * @param int $ticket_type_id
     * @return SummitTicketType
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deleteTicketType(Summit $summit, $ticket_type_id)
    {
        return $this->tx_service->transaction(function() use ($summit, $ticket_type_id){

            $ticket_type = $summit->getTicketTypeById($ticket_type_id);

            if(is_null($ticket_type)){
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.SummitTicketTypeService.deleteTicketType.TicketTypeNotFound',
                        [
                            'ticket_type_id' => $ticket_type_id,
                            'summit_id'      => $summit->getId()
                        ]
                    )
                );
            }

            Event::fire
            (
                new SummitTicketTypeDeleted
                (
                    $ticket_type->getId(),
                    $ticket_type->getSummitId()
                )
            );

            $summit->removeTicketType($ticket_type);
        });
    }

    /**
     * @param Summit $summit
     * @return SummitTicketType[]
     * @throws ValidationException
     */
    public function seedSummitTicketTypesFromEventBrite(Summit $summit){

        return $this->tx_service->transaction(function() use($summit){
            $res                = [];
            $external_summit_id = $summit->getExternalSummitId();

            if(empty($external_summit_id)){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.SummitTicketTypeService.seedSummitTicketTypesFromEventBrite.MissingExternalId',
                        [
                            'summit_id' => $summit->getId()
                        ]
                    )
                );
            }

            $response = $this->eventbrite_api->getTicketTypes($summit);

            if (!isset($response['ticket_classes'])) return $res;

            $ticket_classes = $response['ticket_classes'];

            foreach ($ticket_classes as $ticket_class) {

                $id              = $ticket_class['id'];
                $old_ticket_type = $summit->getTicketTypeByExternalId($id);

                if (!is_null($old_ticket_type)) {

                    $old_ticket_type->setName(trim($ticket_class['name']));
                    $old_ticket_type->setDescription(isset($ticket_class['description']) ? trim($ticket_class['description']) : '');
                    continue;
                }

                $new_ticket_type = new SummitTicketType();
                $new_ticket_type->setExternalId($id);
                $new_ticket_type->setName($ticket_class['name']);
                $new_ticket_type->setDescription(isset($ticket_class['description']) ? trim($ticket_class['description']) : '');
                $summit->addTicketType($new_ticket_type);
                $res[] = $new_ticket_type;
            }

            foreach ($res as $ticket_type){
                Event::fire
                (
                    new SummitTicketTypeInserted
                    (
                        $ticket_type->getId(),
                        $ticket_type->getSummitId()
                    )
                );
            }

            return $res;
        });
    }
}