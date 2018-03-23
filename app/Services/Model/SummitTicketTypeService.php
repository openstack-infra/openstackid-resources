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
use App\Models\Foundation\Summit\Factories\SummitTicketTypeFactory;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\ISummitTicketTypeRepository;
use models\summit\Summit;
use models\summit\SummitTicketType;
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
     * SummitTicketTypeService constructor.
     * @param ISummitTicketTypeRepository $repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitTicketTypeRepository $repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->repository = $repository;
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
        return $this->tx_service->transaction(function() use ($summit, $data){

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
    }
}