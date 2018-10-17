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
use App\Models\Foundation\Main\CountryCodes;
use App\Models\Foundation\Main\Repositories\ILanguageRepository;
use App\Models\Foundation\Summit\Factories\PresentationSpeakerSummitAssistanceConfirmationRequestFactory;
use App\Models\Foundation\Summit\Repositories\IPresentationSpeakerSummitAssistanceConfirmationRequestRepository;
use App\Models\Foundation\Summit\Repositories\ISpeakerActiveInvolvementRepository;
use App\Models\Foundation\Summit\Repositories\ISpeakerOrganizationalRoleRepository;
use App\Services\Model\AbstractService;
use App\Services\Model\IFolderService;
use Illuminate\Http\UploadedFile;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\EmailCreationRequest;
use models\main\File;
use models\main\IEmailCreationRequestRepository;
use models\main\IMemberRepository;
use models\main\MemberPromoCodeEmailCreationRequest;
use models\main\SpeakerCreationEmailCreationRequest;
use models\main\SpeakerSelectionAnnouncementEmailCreationRequest;
use models\summit\factories\SpeakerSelectionAnnouncementEmailTypeFactory;
use models\summit\ISpeakerRegistrationRequestRepository;
use models\summit\ISpeakerRepository;
use models\summit\ISpeakerSummitRegistrationPromoCodeRepository;
use models\summit\PresentationSpeaker;
use models\summit\PresentationSpeakerSummitAssistanceConfirmationRequest;
use models\summit\SpeakerExpertise;
use models\summit\SpeakerOrganizationalRole;
use models\summit\SpeakerPresentationLink;
use models\summit\SpeakerRegistrationRequest;
use models\summit\SpeakerSummitRegistrationPromoCode;
use models\summit\SpeakerTravelPreference;
use models\summit\Summit;
use App\Http\Utils\FileUploader;
/**
 * Class SpeakerService
 * @package services\model
 */
final class SpeakerService
    extends AbstractService
    implements ISpeakerService
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
     * @var IFolderService
     */
    private $folder_service;

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
     * @var IPresentationSpeakerSummitAssistanceConfirmationRequestRepository
     */
    private $speakers_assistance_repository;

    /**
     * @var ILanguageRepository
     */
    private $language_repository;

    /**
     * @var ISpeakerOrganizationalRoleRepository
     */
    private $speaker_organizational_role_repository;

    /**
     * @var ISpeakerActiveInvolvementRepository
     */
    private $speaker_involvement_repository;

    /**
     * SpeakerService constructor.
     * @param ISpeakerRepository $speaker_repository
     * @param IMemberRepository $member_repository
     * @param ISpeakerRegistrationRequestRepository $speaker_registration_request_repository
     * @param ISpeakerSummitRegistrationPromoCodeRepository $registration_code_repository
     * @param IEmailCreationRequestRepository $email_creation_request_repository
     * @param IFolderService $folder_service
     * @param IPresentationSpeakerSummitAssistanceConfirmationRequestRepository $speakers_assistance_repository
     * @param ILanguageRepository $language_repository
     * @param ISpeakerOrganizationalRoleRepository $speaker_organizational_role_repository
     * @param ISpeakerActiveInvolvementRepository $speaker_involvement_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        ISpeakerRepository $speaker_repository,
        IMemberRepository $member_repository,
        ISpeakerRegistrationRequestRepository $speaker_registration_request_repository,
        ISpeakerSummitRegistrationPromoCodeRepository $registration_code_repository,
        IEmailCreationRequestRepository $email_creation_request_repository,
        IFolderService $folder_service,
        IPresentationSpeakerSummitAssistanceConfirmationRequestRepository $speakers_assistance_repository,
        ILanguageRepository $language_repository,
        ISpeakerOrganizationalRoleRepository $speaker_organizational_role_repository,
        ISpeakerActiveInvolvementRepository $speaker_involvement_repository,
        ITransactionService $tx_service
    )
    {
        parent::__construct($tx_service);
        $this->speaker_repository                      = $speaker_repository;
        $this->member_repository                       = $member_repository;
        $this->folder_service                          = $folder_service;
        $this->speaker_registration_request_repository = $speaker_registration_request_repository;
        $this->registration_code_repository            = $registration_code_repository;
        $this->email_creation_request_repository       = $email_creation_request_repository;
        $this->speakers_assistance_repository          = $speakers_assistance_repository;
        $this->language_repository                     = $language_repository;
        $this->speaker_organizational_role_repository  = $speaker_organizational_role_repository;
        $this->speaker_involvement_repository          = $speaker_involvement_repository;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @return PresentationSpeaker
     */
    public function addSpeakerBySummit(Summit $summit, array $data){

        return $this->tx_service->transaction(function() use($data, $summit){

            $speaker = new PresentationSpeaker();
            $speaker->setCreatedFromApi(true);
            $member_id = 0;

            if(!isset($data['email']) && !isset($data['member_id']))
                throw
                new ValidationException
                ("you must provide an email or a member_id in order to create a speaker!");

            if(isset($data['member_id']) && intval($data['member_id']) > 0){
                $member_id = intval($data['member_id']);
                $member    =  $this->member_repository->getById($member_id);
                if(is_null($member))
                    throw new EntityNotFoundException(sprintf("member id %s does not exists!", $member_id));

                $existent_speaker = $this->speaker_repository->getByMember($member);
                if(!is_null($existent_speaker))
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "member_id %s already has assigned an speaker!",
                            $member_id
                        )
                    );


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
                PresentationSpeakerSummitAssistanceConfirmationRequestFactory::build($summit, $speaker, $data)
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
            // check if our speaker already has an assigned code for this summit ...
            $existent_code = $this->registration_code_repository->getBySpeakerAndSummit($speaker, $summit);

            // we are trying to update the promo code with another one ....
            if (!is_null($existent_code) && $reg_code !== $existent_code->getCode() && $existent_code->isRedeemed()) {
                throw new ValidationException(sprintf(
                    'speaker has been already assigned to another registration code (%s) already redeemed!', $existent_code->getCode()
                ));
            }

            if(!is_null($existent_code) && $reg_code == $existent_code->getCode()) return $existent_code;

            // check if reg code is assigned already to another speaker ...
            if ($assigned_code = $this->registration_code_repository->getAssignedCode($reg_code, $summit)) {

                if($assigned_code->getSpeaker()->getId() != $speaker->getId())
                    throw new ValidationException(sprintf(
                        'there is another speaker with that code for this summit ( speaker id %s )', $assigned_code->getSpeaker()->getId()
                    ));
            }
            // check is not assigned already
            $new_code = $this->registration_code_repository->getNotAssignedCode($reg_code, $summit);

            if (is_null($new_code)) {
                // create it
                $new_code = new SpeakerSummitRegistrationPromoCode();
                $new_code->setSummit($summit);
                $new_code->setCode($reg_code);
                $new_code->setSourceAdmin();
                // create email request
                $email_request = new MemberPromoCodeEmailCreationRequest();
                $email_request->setPromoCode($new_code);
                $email_request->setEmail($speaker->getEmail());
                $email_request->setName($speaker->getFullName());
                $this->email_creation_request_repository->add($email_request);
            }

            $speaker->addPromoCode($new_code);
            if(!is_null($existent_code)){
                $speaker->removePromoCode($existent_code);
            }
            return $new_code;
        });
    }

    /**
     * @param PresentationSpeaker $speaker
     * @param array $data
     * @return PresentationSpeaker
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

        if(isset($data['notes']))
            $speaker->setNotes(trim($data['notes']));

        if(isset($data['available_for_bureau']))
            $speaker->setAvailableForBureau(boolval($data['available_for_bureau']));

        if(isset($data['funded_travel']))
            $speaker->setFundedTravel(boolval($data['funded_travel']));

        if(isset($data['willing_to_travel']))
            $speaker->setWillingToTravel(boolval($data['willing_to_travel']));

        if(isset($data['willing_to_present_video']))
            $speaker->setWillingToPresentVideo(boolval($data['willing_to_present_video']));

        if(isset($data['org_has_cloud']))
            $speaker->setOrgHasCloud(boolval($data['org_has_cloud']));

        if(isset($data['country']))
            $speaker->setCountry(trim($data['country']));

        return $speaker;
    }

    /**
     * @param PresentationSpeaker $speaker
     * @param array $data
     * @return PresentationSpeaker
     */
    private function updateSpeakerRelations(PresentationSpeaker $speaker, array $data){

        // other_presentation_links

        if(isset($data['other_presentation_links']) && is_array($data['other_presentation_links'])){
            $speaker->clearOtherPresentationLinks();
            foreach($data['other_presentation_links'] as $link) {
                $speaker->addOtherPresentationLink(new SpeakerPresentationLink($link));
            }
        }
        // languages

        if(isset($data['languages']) && is_array($data['languages'])){
            $speaker->clearLanguages();
            foreach($data['languages'] as $lang_id) {
                $language = $this->language_repository->getById(intval($lang_id));
                if(is_null($language)) continue;
                $speaker->addLanguage($language);
            }
        }

        // travel_preferences

        if(isset($data['travel_preferences']) && is_array($data['travel_preferences'])){
            $speaker->clearTravelPreferences();
            foreach($data['travel_preferences'] as $country) {
                if(!isset(CountryCodes::$iso_3166_countryCodes[$country])) continue;
                $speaker->addTravelPreference(new SpeakerTravelPreference($country));
            }
        }
        // areas_of_expertise

        if(isset($data['areas_of_expertise']) && is_array($data['areas_of_expertise'])){
            $speaker->clearAreasOfExpertise();
            foreach($data['areas_of_expertise'] as $expertise) {
                $speaker->addAreaOfExpertise(new SpeakerExpertise(trim($expertise)));
            }
        }

        // organizational_roles

        if(isset($data['organizational_roles']) && is_array($data['organizational_roles'])){
            $speaker->clearOrganizationalRoles();
            foreach($data['organizational_roles'] as $org_role_id) {
                $role = $this->speaker_organizational_role_repository->getById(intval($org_role_id));
                if(is_null($role)) continue;
                $speaker->addOrganizationalRole($role);
            }

            // other
            if(isset($data['other_organizational_rol'])){
                $role = $this->speaker_organizational_role_repository->getByRole(trim($data['other_organizational_rol']));
                if(is_null($role)){
                    // create it
                    $role = new SpeakerOrganizationalRole(trim($data['other_organizational_rol']));
                    $this->speaker_organizational_role_repository->add($role);
                }
                $speaker->addOrganizationalRole($role);
            }
        }

        // active_involvements

        if(isset($data['active_involvements']) && is_array($data['active_involvements'])){
            $speaker->clearActiveInvolvements();
            foreach($data['active_involvements'] as $involvement_id) {
                $involvement = $this->speaker_involvement_repository->getById(intval($involvement_id));
                if(is_null($involvement)) continue;
                $speaker->addActiveInvolvement($involvement);
            }
        }

        return $speaker;
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @param PresentationSpeaker $speaker
     * @return PresentationSpeaker
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function updateSpeakerBySummit(Summit $summit, PresentationSpeaker $speaker, array $data)
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

           // get summit assistance
           $summit_assistance = $speaker->getAssistanceFor($summit);
           // if does not exists create it
           if(is_null($summit_assistance)){
               $summit_assistance = $speaker->buildAssistanceFor($summit);
               $speaker->addSummitAssistance($summit_assistance);
           }

           PresentationSpeakerSummitAssistanceConfirmationRequestFactory::populate($summit_assistance, $data);

           $reg_code = isset($data['registration_code']) ? trim($data['registration_code']) : null;
           if(!empty($reg_code)){
               $this->registerSummitPromoCodeByValue($speaker, $summit, $reg_code);
           }
           return $speaker;
       });
    }

    /**
     * @param int $speaker_id
     * @param UploadedFile $file
     * @param int $max_file_size
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return File
     */
    public function addSpeakerPhoto($speaker_id, UploadedFile $file, $max_file_size = 10485760)
    {
        return $this->tx_service->transaction(function () use ($speaker_id, $file, $max_file_size) {

            $allowed_extensions = ['png','jpg','jpeg','gif','pdf'];

            $speaker = $this->speaker_repository->getById($speaker_id);

            if (is_null($speaker)) {
                throw new EntityNotFoundException('speaker not found!');
            }

            if(!in_array($file->extension(), $allowed_extensions)){
                throw new ValidationException("file does not has a valid extension ('png','jpg','jpeg','gif','pdf').");
            }

            if($file->getSize() > $max_file_size)
            {
                throw new ValidationException(sprintf( "file exceeds max_file_size (%s MB).", ($max_file_size/1024)/1024));
            }

            $uploader = new FileUploader($this->folder_service);
            $photo    = $uploader->build($file, 'profile-images', true);
            $speaker->setPhoto($photo);

            return $photo;
        });
    }

    /**
     * @param PresentationSpeaker $speaker_from
     * @param PresentationSpeaker $speaker_to
     * @param array $data
     * @return void
     */
    public function merge(PresentationSpeaker $speaker_from, PresentationSpeaker $speaker_to, array $data)
    {
        return $this->tx_service->transaction(function () use ($speaker_from, $speaker_to, $data) {

            if($speaker_from->getIdentifier() == $speaker_to->getIdentifier())
                throw new ValidationException("You can not merge the same speaker!");
            // bio
            if (!isset($data['bio'])) throw new ValidationException("bio field is required");
            $speaker_id = intval($data['bio']);
            $speaker_to->setBio($speaker_id == $speaker_from->getId() ? $speaker_from->getBio() : $speaker_to->getBio());

            // first_name
            if (!isset($data['first_name'])) throw new ValidationException("first_name field is required");
            $speaker_id = intval($data['first_name']);
            $speaker_to->setFirstName($speaker_id == $speaker_from->getId() ? $speaker_from->getFirstName() : $speaker_to->getFirstName());

            // last_name
            if (!isset($data['last_name'])) throw new ValidationException("last_name field is required");
            $speaker_id = intval($data['last_name']);
            $speaker_to->setLastName($speaker_id == $speaker_from->getId() ? $speaker_from->getLastName() : $speaker_to->getLastName());

            // title
            if (!isset($data['title'])) throw new ValidationException("title field is required");
            $speaker_id = intval($data['title']);
            $speaker_to->setTitle($speaker_id == $speaker_from->getId() ? $speaker_from->getTitle() : $speaker_to->getTitle());

            // irc
            if (!isset($data['irc'])) throw new ValidationException("irc field is required");
            $speaker_id = intval($data['irc']);
            $speaker_to->setIrcHandle($speaker_id == $speaker_from->getId() ? $speaker_from->getIrcHandle() : $speaker_to->getIrcHandle());

            // twitter
            if (!isset($data['twitter'])) throw new ValidationException("twitter field is required");
            $speaker_id = intval($data['twitter']);
            $speaker_to->setTwitterName($speaker_id == $speaker_from->getId() ? $speaker_from->getTwitterName() : $speaker_to->getTwitterName());

            // pic
            try {
                if (!isset($data['pic'])) throw new ValidationException("pic field is required");
                $speaker_id = intval($data['pic']);
                $speaker_to->setPhoto($speaker_id == $speaker_from->getId() ? $speaker_from->getPhoto() : $speaker_to->getPhoto());
            }
            catch (\Exception $ex){

            }
            // registration_request
            try {
                if (!isset($data['registration_request'])) throw new ValidationException("registration_request field is required");
                $speaker_id = intval($data['registration_request']);
                $speaker_to->setRegistrationRequest($speaker_id == $speaker_from->getId() ? $speaker_from->getRegistrationRequest() : $speaker_to->getRegistrationRequest());
            }
            catch (\Exception $ex){

            }
            // member
            try {
                if (!isset($data['member'])) throw new ValidationException("member field is required");
                $speaker_id = intval($data['member']);
                $speaker_to->setMember($speaker_id == $speaker_from->getId() ? $speaker_from->getMember() : $speaker_to->getMember());
            }
            catch (\Exception $ex){

            }
            // presentations

            foreach ($speaker_from->getAllPresentations(false) as $presentation){
                $speaker_to->addPresentation($presentation);
            }

            foreach ($speaker_from->getAllModeratedPresentations(false) as $presentation){
                $speaker_to->addModeratedPresentation($presentation);
            }

            // languages

            foreach($speaker_from->getLanguages() as $language){
                $speaker_to->addLanguage($language);
            }

            // promo codes

            foreach($speaker_from->getPromoCodes() as $code){
                $speaker_to->addPromoCode($code);
            }

            // summit assistances

            foreach($speaker_from->getSummitAssistances() as $assistance){
                $speaker_to->addSummitAssistance($assistance);
            }

            // presentation links

            foreach($speaker_from->getOtherPresentationLinks() as $link){
                $speaker_to->addOtherPresentationLink($link);
            }

            // travel preferences

            foreach ($speaker_from->getTravelPreferences() as $travel_preference){
                $speaker_to->addTravelPreference($travel_preference);
            }

            // areas of expertise

            foreach ($speaker_from->getAreasOfExpertise() as $areas_of_expertise){
                $speaker_to->addAreaOfExpertise($areas_of_expertise);
            }

            // roles

            foreach ($speaker_from->getOrganizationalRoles() as $role){
                $speaker_to->addOrganizationalRole($role);
            }
            $speaker_from->clearOrganizationalRoles();

            // involvements

            foreach ($speaker_from->getActiveInvolvements() as $involvement){
                $speaker_to->addActiveInvolvement($involvement);
            }

            $speaker_from->clearActiveInvolvements();

            $this->speaker_repository->delete($speaker_from);
        });
    }

    /**
     * @param array $data
     * @throws ValidationException
     * @return PresentationSpeaker
     */
    public function addSpeaker(array $data){

        return $this->tx_service->transaction(function() use($data){

            $speaker = new PresentationSpeaker();
            $speaker->setCreatedFromApi(true);
            $member_id = 0;

            if(!isset($data['email']) && !isset($data['member_id']))
                throw
                new ValidationException
                ("you must provide an email or a member_id in order to create a speaker!");

            if(isset($data['member_id']) && intval($data['member_id']) > 0){
                $member_id = intval($data['member_id']);
                $member    =  $this->member_repository->getById($member_id);
                if(is_null($member))
                    throw new EntityNotFoundException(sprintf("member id %s does not exists!", $member_id));

                $existent_speaker = $this->speaker_repository->getByMember($member);
                if(!is_null($existent_speaker))
                    throw new ValidationException
                    (
                        sprintf
                        (
                            "member_id %s already has assigned an speaker!",
                            $member_id
                        )
                    );

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

            $this->speaker_repository->add($this->updateSpeakerRelations($speaker, $data));

            $email_request = new SpeakerCreationEmailCreationRequest();
            $email_request->setSpeaker($speaker);
            $this->email_creation_request_repository->add($email_request);

            return $speaker;
        });
    }

    /**
     * @param array $data
     * @param PresentationSpeaker $speaker
     * @return PresentationSpeaker
     * @throws ValidationException
     */
    public function updateSpeaker(PresentationSpeaker $speaker, array $data)
    {
        return $this->tx_service->transaction(function() use ($speaker, $data){
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

            return $this->updateSpeakerRelations($this->updateSpeakerMainData($speaker, $data), $data);

        });
    }

    /**
     * @param int $speaker_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSpeaker($speaker_id)
    {
        return $this->tx_service->transaction(function() use($speaker_id){
            $speaker = $this->speaker_repository->getById($speaker_id);
            if(is_null($speaker))
                throw new EntityNotFoundException;

            $this->speaker_repository->delete($speaker);
        });
    }

    /**
     * @param Summit $summit
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function addSpeakerAssistance(Summit $summit, array $data)
    {
        return $this->tx_service->transaction(function() use($data, $summit){

            $speaker_id = intval($data['speaker_id']);
            $speaker    = $this->speaker_repository->getById($speaker_id);

            if(is_null($speaker))
                throw new EntityNotFoundException(trans('not_found_errors.add_speaker_assistance_speaker_not_found', ['speaker_id' => $speaker_id]));

            if(!$speaker->isSpeakerOfSummit($summit)){
                throw new ValidationException(trans('validation_errors.add_speaker_assistance_speaker_is_not_on_summit',
                    [
                        'speaker_id' => $speaker_id,
                        'summit_id'  => $summit->getId()
                    ]
                ));
            }

            if($speaker->hasAssistanceFor($summit))
                throw new ValidationException(trans('validation_errors.add_speaker_assistance_speaker_already_has_assistance',
                    [
                        'speaker_id' => $speaker_id,
                        'summit_id'  => $summit->getId()
                    ]
                ));

            $assistance = PresentationSpeakerSummitAssistanceConfirmationRequestFactory::build
            (
                $summit,
                $speaker,
                $data
            );

            $speaker->addSummitAssistance($assistance);

            return $assistance;
        });
    }

    /**
     * @param Summit $summit
     * @param int $assistance_id
     * @param array $data
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function updateSpeakerAssistance(Summit $summit, $assistance_id, array $data)
    {
        return $this->tx_service->transaction(function() use($summit, $assistance_id , $data){
            $assistance = $this->speakers_assistance_repository->getById($assistance_id);
            if(is_null($assistance))
                throw new EntityNotFoundException;

            if($assistance->getSummitId() != $summit->getId()){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.speaker_assistance_does_not_belongs_to_summit',
                        [
                            'assistance_id' => $assistance_id,
                            'summit_id'     => $summit->getId()
                        ]
                    )
                );
            }

            return PresentationSpeakerSummitAssistanceConfirmationRequestFactory::populate
            (
                $assistance,
                $data
            );

        });
    }

    /**
     * @param Summit $summit
     * @param int $assistance_id
     * @throws ValidationException
     * @throws EntityNotFoundException
     * @return void
     */
    public function deleteSpeakerAssistance(Summit $summit, $assistance_id){
        return $this->tx_service->transaction(function() use($summit, $assistance_id){

            $assistance = $this->speakers_assistance_repository->getById($assistance_id);

            if(is_null($assistance))
                throw new EntityNotFoundException;

            if($assistance->getSummitId() != $summit->getId()){
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.speaker_assistance_does_not_belongs_to_summit',
                        [
                            'assistance_id' => $assistance_id,
                            'summit_id'     => $summit->getId()
                        ]
                    )
                );
            }

            if($assistance->isConfirmed())
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.speaker_assistance_delete_already_confirmed',
                        [
                            'assistance_id' => $assistance_id,
                            'speaker_id'    => $assistance->getSpeakerId()
                        ]
                    )
                );

            $this->speakers_assistance_repository->delete($assistance);
        });
    }

    /**
     * @param Summit $summit
     * @param int $assistance_id
     * @return EmailCreationRequest
     * @throws EntityNotFoundException
     * @throws ValidationException
     */
    public function sendSpeakerSummitAssistanceAnnouncementMail(Summit $summit, $assistance_id)
    {
        return $this->tx_service->transaction(function() use($summit, $assistance_id){

            $speaker_assistance = $summit->getSpeakerAssistanceById($assistance_id);

            if(is_null($speaker_assistance))
                throw new EntityNotFoundException
                (
                    trans
                    (
                        'not_found_errors.send_speaker_summit_assistance_announcement_mail_not_found_assistance',
                        [
                            'summit_id' => $summit->getId(),
                            'assistance_id' => $assistance_id
                        ]
                    )
                );
            $speaker = $speaker_assistance->getSpeaker();

            $role    = $speaker->isModeratorFor($summit) ?
                PresentationSpeaker::RoleModerator : PresentationSpeaker::RoleSpeaker;

            /*
            if($speaker->announcementEmailAlreadySent($summit))
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.send_speaker_summit_assistance_announcement_mail_email_already_sent',
                        [
                            'summit_id'  => $summit->getId(),
                            'speaker_id' => $speaker->getId()
                        ]
                    )
                );
            */

            $promo_code = $speaker->getPromoCodeFor($summit);

            if(is_null($promo_code)){
                // try to get a new one
                $has_published =
                    $speaker->hasPublishedRegularPresentations($summit, $role, true, $summit->getExcludedCategoriesForAcceptedPresentations()) ||
                    $speaker->hasPublishedLightningPresentations($summit, $role, true, $summit->getExcludedCategoriesForAcceptedPresentations());
                $has_alternate = $speaker->hasAlternatePresentations($summit, $role, true, $summit->getExcludedCategoriesForAlternatePresentations());

                if ($has_published) //get approved code
                {
                    $promo_code = $this->registration_code_repository->getNextAvailableByType
                    (
                        $summit,
                        SpeakerSummitRegistrationPromoCode::TypeAccepted
                    );
                    if (is_null($promo_code))
                        throw new ValidationException
                        (
                            trans
                            (
                                'validation_errors.send_speaker_summit_assistance_announcement_mail_run_out_promo_code',
                                [
                                    'summit_id'  => $summit->getId(),
                                    'speaker_id' => $speaker->getId(),
                                    'type'       => SpeakerSummitRegistrationPromoCode::TypeAccepted
                                ]
                            )
                        );
                    $speaker->addPromoCode($promo_code);
                }
                else if ($has_alternate) // get alternate code
                {
                    $promo_code = $this->registration_code_repository->getNextAvailableByType
                    (
                        $summit,
                        SpeakerSummitRegistrationPromoCode::TypeAlternate
                    );
                    if (is_null($promo_code))
                        throw new ValidationException
                        (
                            trans
                            (
                                'validation_errors.send_speaker_summit_assistance_announcement_mail_run_out_promo_code',
                                [
                                    'summit_id'  => $summit->getId(),
                                    'speaker_id' => $speaker->getId(),
                                    'type'       => SpeakerSummitRegistrationPromoCode::TypeAccepted
                                ]
                            )

                        );
                    $speaker->addPromoCode($promo_code);
                }
            }

            if (is_null($promo_code))
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.send_speaker_summit_assistance_promo_code_not_set',
                        [
                            'summit_id'  => $summit->getId(),
                            'speaker_id' => $speaker->getId(),
                        ]
                    )

                );

            $type = SpeakerSelectionAnnouncementEmailTypeFactory::build($summit, $speaker, $role);

            if($promo_code->isRedeemed())
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.send_speaker_summit_assistance_announcement_mail_code_already_redeemed',
                        [
                            'promo_code' => $promo_code->getCode()
                        ]
                    )
                );

            if(!SpeakerSelectionAnnouncementEmailCreationRequest::isValidType($type))
                throw new ValidationException
                (
                    trans
                    (
                        'validation_errors.send_speaker_summit_assistance_announcement_mail_invalid_mail_type',
                        [
                            'mail_type' => $type
                        ]
                    )
                );

            // create email request
            $email_request = new SpeakerSelectionAnnouncementEmailCreationRequest();
            $email_request->setPromoCode($promo_code);
            $email_request->setSummit($summit);
            $email_request->setSpeaker($speaker);
            $email_request->setType($type);
            $email_request->setSpeakerRole($role);
            $this->email_creation_request_repository->add($email_request);
            $promo_code->setEmailSent(true);
            return $email_request;

        });
    }
}