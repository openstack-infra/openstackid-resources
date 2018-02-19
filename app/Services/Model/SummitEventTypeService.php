<?php namespace App\Services;
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
use App\Models\Foundation\Summit\Factories\SummitEventTypeFactory;
use App\Models\Foundation\Summit\Repositories\ISummitEventTypeRepository;
use App\Services\Model\ISummitEventTypeService;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\summit\Summit;
use models\summit\SummitEventType;

/**
 * Class SummitEventTypeService
 * @package App\Services
 */
final class SummitEventTypeService implements ISummitEventTypeService
{

    /**
     * @var ITransactionService
     */
    private $tx_manager;

    /**
     * @var ISummitEventTypeRepository
     */
    private $repository;

    /**
     * SummitEventTypeService constructor.
     * @param ISummitEventTypeRepository $repository
     * @param ITransactionService $tx_manager
     */
    public function __construct
    (
        ISummitEventTypeRepository $repository,
        ITransactionService $tx_manager
    )
    {
        $this->tx_manager = $tx_manager;
        $this->repository = $repository;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @return SummitEventType
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addEventType(Summit $summit, array $data)
    {
        return $this->tx_manager->transaction(function() use($summit, $data){

            $type = trim($data['name']);

            if($summit->hasEventType($type)){
                throw new ValidationException(sprintf("event type %s already exist on summit id %s", $type, $summit->getId()));
            }

            $event_type = SummitEventTypeFactory::build($summit, $data);

            if(is_null($event_type))
                throw new ValidationException(sprintf("class_name %s is invalid", $data['class_name']));

            return $event_type;

        });
    }
}