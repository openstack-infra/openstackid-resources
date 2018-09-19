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
use models\main\Affiliation;
use models\main\IOrganizationRepository;
use models\main\Member;
use DateTime;
/**
 * Class MemberService
 * @package App\Services\Model
 */
final class MemberService
    extends AbstractService
    implements IMemberService
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
     * @param Member $member
     * @param int $affiliation_id
     * @param array $data
     * @return Affiliation
     */
    public function updateAffiliation(Member $member, $affiliation_id, array $data)
    {
        return $this->tx_service->transaction(function() use($member, $affiliation_id, $data){
            $affiliation = $member->getAffiliationById($affiliation_id);
            if(is_null($affiliation))
                throw new EntityNotFoundException(sprintf("affiliation id %s does not belongs to member id %s", $affiliation_id, $member->getId()));

            if(isset($data['is_current'])) {
                $affiliation->setIsCurrent(boolval($data['is_current']));
            }

            if(isset($data['start_date'])) {
                $start_date = intval($data['start_date']);
                $affiliation->setStartDate(new DateTime("@$start_date"));
            }

            if(!$affiliation->isCurrent() && isset($data['end_date'])) {
                $end_date = intval($data['end_date']);
                $affiliation->setEndDate($end_date > 0 ? new DateTime("@$end_date") : null);
            }

            if(isset($data['organization_id'])) {
                $org = $this->organization_repository->getById(intval($data['organization_id']));
                if(is_null($org))
                    throw new EntityNotFoundException(sprintf("organization id %s not found", $data['organization_id']));
                $affiliation->setOrganization($org);
            }

            if(isset($data['job_title'])) {
                $affiliation->setJobTitle(trim($data['job_title']));
            }

            if($affiliation->isCurrent()){
                $affiliation->clearEndDate();
            }

            return $affiliation;
        });
    }

    /**
     * @param Member $member
     * @param $affiliation_id
     * @return void
     */
    public function deleteAffiliation(Member $member, $affiliation_id)
    {
        return $this->tx_service->transaction(function() use($member, $affiliation_id){
            $affiliation = $member->getAffiliationById($affiliation_id);
            if(is_null($affiliation))
                throw new EntityNotFoundException(sprintf("affiliation id %s does not belongs to member id %s", $affiliation_id, $member->getId()));

            $member->removeAffiliation($affiliation);
        });
    }

    /**
     * @param Member $member
     * @param int $rsvp_id
     * @return void
     */
    public function deleteRSVP(Member $member, $rsvp_id)
    {
        return $this->tx_service->transaction(function() use($member, $rsvp_id){
            $rsvp = $member->getRsvpById($rsvp_id);
            if(is_null($rsvp))
                throw new EntityNotFoundException(sprintf("rsvp id %s does not belongs to member id %s", $rsvp_id, $member->getId()));

            $member->removeRsvp($rsvp);
        });
    }

    /**
     * @param Member $member
     * @param array $data
     * @return Affiliation
     */
    public function addAffiliation(Member $member, array $data)
    {
        return $this->tx_service->transaction(function() use($member, $data){

            $affiliation = new Affiliation();

            if(isset($data['is_current']))
                $affiliation->setIsCurrent(boolval($data['is_current']));
            if(isset($data['start_date'])) {
                $start_date = intval($data['start_date']);
                $affiliation->setStartDate(new DateTime("@$start_date"));
            }
            if(isset($data['end_date'])) {
                $end_date = intval($data['end_date']);
                $affiliation->setEndDate($end_date > 0 ? new DateTime("@$end_date") : null);
            }
            if(isset($data['organization_id'])) {
                $org = $this->organization_repository->getById(intval($data['organization_id']));
                if(is_null($org))
                    throw new EntityNotFoundException(sprintf("organization id %s not found", $data['organization_id']));
                $affiliation->setOrganization($org);
            }

            if(isset($data['job_title'])) {
                $affiliation->setJobTitle(trim($data['job_title']));
            }

            if($affiliation->isCurrent() && $affiliation->getEndDate() != null)
                throw new ValidationException
                (
                    sprintf
                    (
                        "in order to set affiliation as current end_date should be null"
                    )
                );

            $member->addAffiliation($affiliation);
            return $affiliation;
        });
    }
}