<?php namespace App\Services\Model;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IOrganizationRepository;
use models\main\Organization;

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

/**
 * Class OrganizationService
 * @package App\Services\Model
 */
final class OrganizationService
    extends AbstractService
    implements IOrganizationService
{


    /**
     * @var IOrganizationRepository
     */
    private $organization_repository;

    /**
     * MemberService constructor.
     * @param IOrganizationRepository $organization_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IOrganizationRepository $organization_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->organization_repository = $organization_repository;
    }

    /**
     * @param array $data
     * @return Organization
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addOrganization(array $data)
    {
        return $this->tx_service->transaction(function () use($data){

            $name = trim( $data['name']);
            $old_organization = $this->organization_repository->getByName($name);
            if(!is_null($old_organization))
                throw new ValidationException(trans("validation_errors.OrganizationService.addOrganization.alreadyExistName", ["name" => $name]));

            $new_organization = new Organization();
            $new_organization->setName($name);
            $this->organization_repository->add($new_organization);
            return $new_organization;
        });
    }

    /**
     * @param array $data
     * @param int $organization_id
     * @return Organization
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updateOrganization(array $data, $organization_id)
    {
        // TODO: Implement updateOrganization() method.
    }

    /**
     * @param int $organization_id
     * @return void
     * @throws EntityNotFoundException
     */
    public function deleteOrganization($organization_id)
    {
        // TODO: Implement deleteOrganization() method.
    }
}