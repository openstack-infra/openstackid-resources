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
use App\Models\Foundation\Summit\Factories\SummitPromoCodeFactory;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\ICompanyRepository;
use models\main\IMemberRepository;
use models\main\Member;
use models\summit\ISpeakerRepository;
use models\summit\ISummitRegistrationPromoCodeRepository;
use models\summit\Summit;
use models\summit\SummitRegistrationPromoCode;
use services\model\ISummitPromoCodeService;
/**
 * Class SummitPromoCodeService
 * @package App\Services\Model
 */
final class SummitPromoCodeService implements ISummitPromoCodeService
{
    /**
     * @var ISummitRegistrationPromoCodeRepository
     */
    private $promo_code_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var ICompanyRepository
     */
    private $company_repository;

    /**
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var ITransactionService
     */
    private $tx_service;

    /**
     * SummitPromoCodeService constructor.
     * @param ISummitRegistrationPromoCodeRepository $promo_code_repository
     * @param IMemberRepository $member_repository
     * @param ICompanyRepository $company_repository
     * @param ISpeakerRepository $speaker_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISummitRegistrationPromoCodeRepository $promo_code_repository,
        IMemberRepository $member_repository,
        ICompanyRepository $company_repository,
        ISpeakerRepository $speaker_repository,
        ITransactionService $tx_service
    )
    {
        $this->promo_code_repository = $promo_code_repository;
        $this->member_repository     = $member_repository;
        $this->company_repository    = $company_repository;
        $this->speaker_repository    = $speaker_repository;
        $this->tx_service            = $tx_service;
    }

    /**
     * @param array $data
     * @return array
     * @throws EntityNotFoundException
     */
    private function getPromoCodeParams(array $data){
        $params     = [];

        if(isset($data['owner_id'])){
            $owner = $this->member_repository->getById(intval($data['owner_id']));
            if(is_null($owner))
                throw new EntityNotFoundException(sprintf("owner_id %s not found", $data['owner_id']));
            $params['owner'] = $owner;
        }

        if(isset($data['speaker_id'])){
            $speaker = $this->speaker_repository->getById(intval($data['speaker_id']));
            if(is_null($speaker))
                throw new EntityNotFoundException(sprintf("speaker_id %s not found", $data['speaker_id']));
            $params['speaker'] = $speaker;
        }


        if(isset($data['sponsor_id'])){
            $sponsor = $this->company_repository->getById(intval($data['sponsor_id']));
            if(is_null($sponsor))
                throw new EntityNotFoundException(sprintf("sponsor_id %s not found", $data['sponsor_id']));
            $params['sponsor'] = $sponsor;
        }

        return $params;
    }
    /**
     * @param Summit $summit
     * @param array $data
     * @param Member $current_user
     * @return SummitRegistrationPromoCode
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function addPromoCode(Summit $summit, array $data, Member $current_user = null)
    {
        return $this->tx_service->transaction(function() use($summit, $data, $current_user){

            $old_promo_code = $summit->getPromoCodeByCode(trim($data['code']));

            if(!is_null($old_promo_code))
                throw new ValidationException(sprintf("promo code %s already exits on summit id %s", trim($data['code']), $summit->getId()));

            $promo_code = SummitPromoCodeFactory::build($summit, $data, $this->getPromoCodeParams($data));
            if(is_null($promo_code))
                throw new ValidationException(sprintf("class_name %s is invalid", $data['class_name']));

            if(!is_null($current_user))
                $promo_code->setCreator($current_user);

            $promo_code->setSourceAdmin();

            return $promo_code;
        });
    }

    /**
     * @param Summit $summit
     * @param int $promo_code_id
     * @param array $data
     * @param Member $current_user
     * @return SummitRegistrationPromoCode
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function updatePromoCode(Summit $summit, $promo_code_id, array $data, Member $current_user = null)
    {
        return $this->tx_service->transaction(function() use($promo_code_id, $summit, $data, $current_user){

            $old_promo_code = $summit->getPromoCodeByCode(trim($data['code']));

            if(!is_null($old_promo_code) && $old_promo_code->getId() != $promo_code_id)
                throw new ValidationException(sprintf("promo code %s already exits on summit id %s for promo code id %s", trim($data['code']), $summit->getId(), $old_promo_code->getId()));

            $promo_code = $summit->getPromoCodeById($promo_code_id);
            if(is_null($promo_code))
                throw new EntityNotFoundException(sprintf("promo code id %s does not belongs to summit id %s", $promo_code_id, $summit->getId()));

            $promo_code = SummitPromoCodeFactory::populate($promo_code, $summit, $data, $this->getPromoCodeParams($data));

            if(!is_null($current_user))
                $promo_code->setCreator($current_user);

            $promo_code->setSourceAdmin();

            return $promo_code;
        });
    }

    /**
     * @param Summit $summit
     * @param int $promo_code_id
     * @return void
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function deletePromoCode(Summit $summit, $promo_code_id)
    {
        return $this->tx_service->transaction(function() use($promo_code_id, $summit){

            $promo_code = $summit->getPromoCodeById($promo_code_id);
            if(is_null($promo_code))
                throw new EntityNotFoundException(sprintf("promo code id %s does not belongs to summit id %s", $promo_code_id, $summit->getId()));

            if ($promo_code->isEmailSent())
                throw new EntityValidationException("Cannot delete a code that has been already sent.");

            if ($promo_code->isRedeemed())
                throw new EntityValidationException("Cannot delete a code that has been already redeemed.");

            $summit->removePromoCode($promo_code);

        });
    }
}