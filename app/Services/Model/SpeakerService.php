<?php namespace services\model;
/**
 * Copyright 2017 OpenStack Foundation
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
use models\main\IEmailCreationRequestRepository;
use models\main\IFolderRepository;
use models\main\IMemberRepository;
use models\main\SpeakerCreationEmailCreationRequest;
use models\summit\ISpeakerRegistrationRequestRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISpeakerSummitRegistrationPromoCodeRepository;
use models\summit\PresentationSpeaker;
use models\summit\PresentationSpeakerSummitAssistanceConfirmationRequest;
use models\summit\SpeakerRegistrationRequest;
use models\summit\SpeakerSummitRegistrationPromoCode;
use models\summit\Summit;

/**
 * Class SpeakerService
 * @package services\model
 */
final class SpeakerService implements ISpeakerService
{
    /**
     * @var ISpeakerRepository
     */
    private $speaker_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * @var IFolderRepository
     */
    private $folder_repository;

    /**
     * @var ISpeakerRegistrationRequestRepository
     */
    private $speaker_registration_request_repository;

    /**
     * @var ISpeakerSummitRegistrationPromoCodeRepository
     */
    private $registration_code_repository;

    /**
     * @var IEmailCreationRequestRepository
     */
    private $email_creation_request_repository;
    /**
     * @var ITransactionService
     */
    private $tx_service;


    /**
     * SpeakerService constructor.
     * @param ISpeakerRepository $speaker_repository
     * @param IMemberRepository $member_repository
     * @param ISpeakerRegistrationRequestRepository $speaker_registration_request_repository
     * @param ISpeakerSummitRegistrationPromoCodeRepository $registration_code_repository
     * @param IEmailCreationRequestRepository $email_creation_request_repository
     * @param IFolderRepository $folder_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISpeakerRepository $speaker_repository,
        IMemberRepository $member_repository,
        ISpeakerRegistrationRequestRepository $speaker_registration_request_repository,
        ISpeakerSummitRegistrationPromoCodeRepository $registration_code_repository,
        IEmailCreationRequestRepository $email_creation_request_repository,
        IFolderRepository $folder_repository,
        ITransactionService $tx_service
    )
    {
        $this->speaker_repository                      = $speaker_repository;
        $this->member_repository                       = $member_repository;
        $this->folder_repository                       = $folder_repository;
        $this->speaker_registration_request_repository = $speaker_registration_request_repository;
        $this->registration_code_repository            = $registration_code_repository;
        $this->email_creation_request_repository       = $email_creation_request_repository;
        $this->tx_service                              = $tx_service;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @return PresentationSpeaker
     */
    public function addSpeaker(Summit $summit, array $data){

        return $this->tx_service->transaction(function() use($data, $summit){

            $speaker = new PresentationSpeaker();
            $speaker->setCreatedFromApi(true);
            $member_id = 0;

            if(!isset($data['email']) && !isset($data['member_id']))
                throw
                new ValidationException
                ("you must provide an email or a member_id in order to create a speaker!");

            if(isset($data['member_id']) && intval($data['member_id']) > 0){
                $member_id        = intval($data['member_id']);
                $existent_speaker = $this->speaker_repository->getByMember($member_id);
                if(!is_null($existent_speaker))
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "member_id %s already has assigned an speaker!",
                            $member_id
                        )
                    );

                $member =  $this->member_repository->getById($member_id);
                if(is_null($member))
                    throw new EntityNotFoundException(sprintf("member id %s does not exists!", $member_id));
                $speaker->setMember($member);
            }

            $this->updateSpeakerMainData($speaker, $data);

            if($member_id === 0 && isset($data['email'])){
                $email  = trim($data['email']);
                $member = $this->member_repository->getByEmail($email);
                if(is_null($member)){
                    $this->registerSpeaker($speaker, $email);
                }
                else
                {
                    $existent_speaker = $this->speaker_repository->getByMember($member);
                    if(!is_null($existent_speaker))
                        throw new ValidationException
                        (
                            sprintf
                            (
                                "member id %s already has assigned a speaker id %s!",
                                $member->getIdentifier(),
                                $existent_speaker->getIdentifier()
                            )
                        );
                   $speaker->setMember($member);
                }
            }

            $speaker->addSummitAssistance(
                $this->updateSummitAssistance($speaker->buildAssistanceFor($summit), $data)
            );

            $reg_code = isset($data['registration_code']) ? trim($data['registration_code']) : null;
            if(!empty($reg_code)){
                $this->registerSummitPromoCodeByValue($speaker, $summit, $reg_code);
            }
            $this->speaker_repository->add($speaker);

            $email_request = new SpeakerCreationEmailCreationRequest();
            $email_request->setSpeaker($speaker);
            $this->email_creation_request_repository->add($email_request);

            return $speaker;
        });
    }

    /**
     * @param PresentationSpeakerSummitAssistanceConfirmationRequest $summit_assistance
     * @param array $data
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    private function updateSummitAssistance(PresentationSpeakerSummitAssistanceConfirmationRequest $summit_assistance, array $data){
        $on_site_phone = isset($data['on_site_phone']) ? trim($data['on_site_phone']) : null;
        $registered    = isset($data['registered']) ? 1 : 0;
        $checked_in    = isset($data['checked_in']) ? 1 : 0;
        $confirmed     = isset($data['confirmed'])  ? 1 : 0;

        $summit_assistance->setOnSitePhone($on_site_phone);
        $summit_assistance->setRegistered($registered);
        $summit_assistance->setIsConfirmed($confirmed);
        $summit_assistance->setCheckedIn($checked_in);

        return $summit_assistance;
    }

    /**
     * @param PresentationSpeaker $speaker
     * @param string $email
     * @return SpeakerRegistrationRequest
     * @throws ValidationException
     */
    private function registerSpeaker(PresentationSpeaker $speaker, $email){

        if($this->speaker_registration_request_repository->existByEmail($email))
            throw new ValidationException(sprintf("email %s already has a Speaker Registration Request", $email));

        $registration_request = new SpeakerRegistrationRequest();
        $registration_request->setEmail($email);

        do {
            $registration_request->generateConfirmationToken();
        }while($this->speaker_registration_request_repository->existByHash($registration_request->getConfirmationHash()));

        $speaker->setRegistrationRequest($registration_request);
        return $registration_request;
    }

    /**
     * @param PresentationSpeaker $speaker
     * @param Summit $summit
     * @param string $reg_code
     * @return SpeakerSummitRegistrationPromoCode
     * @throws ValidationException
     */
    public function registerSummitPromoCodeByValue(PresentationSpeaker $speaker, Summit $summit, $reg_code){

        return $this->tx_service->transaction(function() use($speaker, $summit, $reg_code) {
            $existent_code = $this->registration_code_repository->getBySpeakerAndSummit($speaker, $summit);

            // we are trying to update the promo code with another one ....
            if ($existent_code && $reg_code !== $existent_code->getCode()) {
                throw new ValidationException(sprintf(
                    'speaker has been already assigned to another registration code (%s)', $existent_code->getCode()
                ));
            }

            if ($assigned_code = $this->registration_code_repository->getAssignedCode($reg_code, $summit)) {
                throw new ValidationException(sprintf(
                    'there is another speaker with that code for this summit ( speaker id %s )', $assigned_code->getSpeaker()->getId()
                ));
            }

            $code = $this->registration_code_repository->getNotAssignedCode($reg_code, $summit);

            if (is_null($code)) {
                //create it
                $code = new SpeakerSummitRegistrationPromoCode();
                $code->setSummit($summit);
                $code->setCode($reg_code);
            }

            $speaker->addPromoCode($code);

            return $code;
        });
    }

    /**
     * @param PresentationSpeaker $speaker
     * @param array $data
     */
    private function updateSpeakerMainData(PresentationSpeaker $speaker, array $data){
        if(isset($data['title']))
            $speaker->setTitle(trim($data['title']));

        if(isset($data['bio']))
            $speaker->setBio(trim($data['bio']));

        if(isset($data['first_name']))
            $speaker->setFirstName(trim($data['first_name']));

        if(isset($data['last_name']))
            $speaker->setLastName(trim($data['last_name']));

        if(isset($data['irc']))
            $speaker->setIrcHandle(trim($data['irc']));

        if(isset($data['twitter']))
            $speaker->setTwitterName(trim($data['twitter']));

    }

    /**
     * @param Summit $summit
     * @param array $data
     * @param PresentationSpeaker $speaker
     * @return PresentationSpeaker
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updateSpeaker(Summit $summit, PresentationSpeaker $speaker, array $data)
    {
       return $this->tx_service->transaction(function() use ($summit, $speaker, $data){
           $member_id = isset($data['member_id']) ? intval($data['member_id']) : null;
           if($member_id > 0)
           {
               $member = $this->member_repository->getById($member_id);
               if(is_null($member))
                   throw new EntityNotFoundException;

               $existent_speaker = $this->speaker_repository->getByMember($member);
               if($existent_speaker && $existent_speaker->getId() !== $speaker->getId())
                   throw new ValidationException
                   (
                       sprintf
                       (
                           "member_id %s already has assigned another speaker id (%s)",
                           $member_id,
                           $existent_speaker->getId()
                       )
                   );

               $speaker->setMember($member);
           }

           $this->updateSpeakerMainData($speaker, $data);

           $summit_assistance = $speaker->getAssistanceFor($summit);
           if(is_null($summit_assistance)){
               $speaker->addSummitAssistance(
                   $this->updateSummitAssistance($speaker->buildAssistanceFor($summit), $data)
               );
           }

           $reg_code = isset($data['registration_code']) ? trim($data['registration_code']) : null;
           if(!empty($reg_code)){
               $this->registerSummitPromoCodeByValue($speaker, $summit, $reg_code);
           }

           return $speaker;
       });
    }
}