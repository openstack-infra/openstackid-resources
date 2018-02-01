<?php namespace models\summit;
/**
 * Copyright 2015 OpenStack Foundation
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
use Doctrine\ORM\Mapping AS ORM;
use App\Events\PresentationSpeakerCreated;
use App\Events\PresentationSpeakerDeleted;
use App\Events\PresentationSpeakerUpdated;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use models\main\File;
use models\main\Member;
use models\utils\PreRemoveEventArgs;
use models\utils\SilverstripeBaseModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Illuminate\Support\Facades\Event;
use Doctrine\Common\Collections\Criteria;
/**
 * @ORM\Entity
 * @ORM\Table(name="PresentationSpeaker")
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSpeakerRepository")
 * @ORM\HasLifecycleCallbacks
 * Class PresentationSpeaker
 * @package models\summit
 */
class PresentationSpeaker extends SilverstripeBaseModel
{

    const AnnouncementEmailAccepted          = 'ACCEPTED';
    const AnnouncementEmailRejected          = 'REJECTED';
    const AnnouncementEmailAlternate         = 'ALTERNATE';
    const AnnouncementEmailAcceptedAlternate = 'ACCEPTED_ALTERNATE';
    const AnnouncementEmailAcceptedRejected  = 'ACCEPTED_REJECTED';
    const AnnouncementEmailAlternateRejected = 'ALTERNATE_REJECTED';
    const RoleSpeaker                        = 'SPEAKER';
    const RoleModerator                      = 'MODERATOR';

    /**
     * @ORM\Column(name="FirstName", type="string")
     */
    private $first_name;

    /**
     * @ORM\Column(name="LastName", type="string")
     */
    private $last_name;

    /**
     * @ORM\Column(name="Title", type="string")
     */
    private $title;

    /**
     * @ORM\Column(name="Bio", type="string")
     */
    private $bio;

    /**
     * @ORM\Column(name="IRCHandle", type="string")
     */
    private $irc_handle;

    /**
     * @ORM\Column(name="TwitterName", type="string")
     */
    private $twitter_name;

    /**
     * @ORM\Column(name="CreatedFromAPI", type="boolean")
     */
    private $created_from_api;

    /**
     * @ORM\Column(name="AvailableForBureau", type="boolean")
     */
    private $available_for_bureau;

    /**
     * @ORM\Column(name="FundedTravel", type="boolean")
     */
    private $funded_travel;

    /**
     * @ORM\Column(name="WillingToTravel", type="boolean")
     */
    private $willing_to_travel;

    /**
     * @ORM\Column(name="Country", type="string")
     */
    private $country;

    /**
     * @ORM\Column(name="WillingToPresentVideo", type="boolean")
     */
    private $willing_to_present_video;

    /**
     * @ORM\Column(name="Notes", type="string")
     */
    private $notes;

    /**
     * @ORM\Column(name="OrgHasCloud", type="boolean")
     */
    private $org_has_cloud;

    /**
     * @ORM\ManyToOne(targetEntity="SpeakerRegistrationRequest", cascade={"persist"}), orphanRemoval=true
     * @ORM\JoinColumn(name="RegistrationRequestID", referencedColumnName="ID")
     * @var SpeakerRegistrationRequest
     */
    private $registration_request;

    /**
     * @ORM\OneToMany(targetEntity="PresentationSpeakerSummitAssistanceConfirmationRequest", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var PresentationSpeakerSummitAssistanceConfirmationRequest[]
     */
    private $summit_assistances;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerSummitRegistrationPromoCode", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var SpeakerSummitRegistrationPromoCode[]
     */
    private $promo_codes;

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\Presentation", mappedBy="speakers")
     * @var Presentation[]
     */
    private $presentations;

    /**
     * @ORM\OneToMany(targetEntity="Presentation", mappedBy="moderator", cascade={"persist"})
     * @var Presentation[]
     */
    private $moderated_presentations;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File", cascade={"persist"})
     * @ORM\JoinColumn(name="PhotoID", referencedColumnName="ID")
     * @var File
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Member
     */
    private $member;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerExpertise", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var SpeakerExpertise[]
     */
    private $areas_of_expertise;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerPresentationLink", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var SpeakerPresentationLink[]
     */
    private $other_presentation_links;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerTravelPreference", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var SpeakerTravelPreference[]
     */
    private $travel_preferences;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerLanguage", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var SpeakerLanguage[]
     */
    private $languages;

    /**
     * @ORM\ManyToMany(targetEntity="SpeakerOrganizationalRole", cascade={"persist"})
     * @ORM\JoinTable(name="PresentationSpeaker_OrganizationalRoles",
     *      joinColumns={@ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="SpeakerOrganizationalRoleID", referencedColumnName="ID")}
     *      )
     * @var SpeakerOrganizationalRole[]
     */
    protected $organizational_roles;

    /**
     * @ORM\ManyToMany(targetEntity="SpeakerOrganizationalRole", cascade={"persist"})
     * @ORM\JoinTable(name="PresentationSpeaker_ActiveInvolvements",
     *      joinColumns={@ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="SpeakerActiveInvolvementID", referencedColumnName="ID")}
     *      )
     * @var SpeakerActiveInvolvement[]
     */
    protected $active_involvements;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerAnnouncementSummitEmail", mappedBy="speaker", cascade={"persist"}, orphanRemoval=true)
     * @var SpeakerAnnouncementSummitEmail[]
     */
    private $announcement_summit_emails;

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @param string $bio
     */
    public function setBio($bio)
    {
        $this->bio = $bio;
    }

    /**
     * @return string
     */
    public function getIrcHandle()
    {
        return $this->irc_handle;
    }

    /**
     * @param string $irc_handle
     */
    public function setIrcHandle($irc_handle)
    {
        $this->irc_handle = $irc_handle;
    }

    /**
     * @return string
     */
    public function getTwitterName()
    {
        return $this->twitter_name;
    }

    /**
     * @param string $twitter_name
     */
    public function setTwitterName($twitter_name)
    {
        $this->twitter_name = $twitter_name;
    }

    public function __construct()
    {
        parent::__construct();
        $this->available_for_bureau       = false;
        $this->willing_to_present_video   = false;
        $this->willing_to_travel          = false;
        $this->funded_travel              = false;
        $this->org_has_cloud              = false;
        $this->presentations              = new ArrayCollection;
        $this->moderated_presentations    = new ArrayCollection;
        $this->summit_assistances         = new ArrayCollection;
        $this->promo_codes                = new ArrayCollection;
        $this->areas_of_expertise         = new ArrayCollection;
        $this->other_presentation_links   = new ArrayCollection;
        $this->travel_preferences         = new ArrayCollection;
        $this->languages                  = new ArrayCollection;
        $this->organizational_roles       = new ArrayCollection;
        $this->active_involvements        = new ArrayCollection;
        $this->announcement_summit_emails = new ArrayCollection;
    }

    /**
     * @param Presentation $presentation
     */
    public function addPresentation(Presentation $presentation){
        $this->presentations->add($presentation);
    }

    public function clearPresentations(){
        foreach($this->presentations as $presentation){
            $presentation->removeSpeaker($this);
        }
        $this->presentations->clear();
    }
    /**
     * @param SpeakerSummitRegistrationPromoCode $code
     * @return $this
     */
    public function addPromoCode(SpeakerSummitRegistrationPromoCode $code){
        $this->promo_codes->add($code);
        $code->setSpeaker($this);
        return $this;
    }

    /**
     * @param SpeakerSummitRegistrationPromoCode $code
     * @return $this
     */
    public function removePromoCode(SpeakerSummitRegistrationPromoCode $code){
        $this->promo_codes->removeElement($code);
        $code->setSpeaker(null);
        return $this;
    }

    /**
     * @return ArrayCollection|SpeakerSummitRegistrationPromoCode[]
     */
    public function getPromoCodes(){
        return $this->promo_codes;
    }

    /**
     * @param Summit $summit
     * @return SpeakerSummitRegistrationPromoCode
     */
    public function getPromoCodeFor(Summit $summit){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('summit', $summit));
        $res = $this->promo_codes->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @param null|int $summit_id
     * @param bool|true $published_ones
     * @return Presentation[]
     */
    public function presentations($summit_id, $published_ones = true)
    {

        return $this->presentations
            ->filter(function($p) use($published_ones, $summit_id){
                $res = $published_ones? $p->isPublished(): true;
                $res &= is_null($summit_id)? true : $p->getSummit()->getId() == $summit_id;
                return $res;
            });
    }

    /**
     * @param Summit $summit
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @return bool
     */
    public function hasPublishedRegularPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = []
    )
    {
        return count($this->getPublishedRegularPresentations($summit, $role, $include_sub_roles, $excluded_tracks)) > 0;
    }


    /**
     * @param Summit $summit
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @return array
     */
    public function getPublishedRegularPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = []
    )
    {
        $list = $this->getPublishedPresentationsByType
        (
            $summit,
            $role,
            [IPresentationType::Keynotes, IPresentationType::Panel, IPresentationType::Presentation],
            true,
            $excluded_tracks
        );

        if($include_sub_roles && $role == PresentationSpeaker::RoleModerator){
            $presentations = $this->getPublishedPresentationsByType
            (
                $summit,
                PresentationSpeaker::RoleSpeaker,
                [IPresentationType::Keynotes, IPresentationType::Panel, IPresentationType::Presentation],
                true,
                $excluded_tracks
            );
            if($presentations) {
                foreach ($presentations as $speaker_presentation)
                    $list[] = $speaker_presentation;
            }
        }

        return $list;
    }

    /**
     * @param Summit $summit
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @return bool
     */
    public function hasPublishedLightningPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = []
    )
    {
        return count($this->getPublishedLightningPresentations
            (
                $summit,
                $role,
                $include_sub_roles,
                $excluded_tracks
            ))> 0;
    }

    /**
     * @param Summit $summit
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @return array
     */
    public function getPublishedLightningPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = []
    )
    {
        $list = $this->getPublishedPresentationsByType($summit, $role, [IPresentationType::LightingTalks], true , $excluded_tracks);

        if($include_sub_roles && $role == PresentationSpeaker::RoleModerator){
            $presentations = $this->getPublishedPresentationsByType($summit, PresentationSpeaker::RoleSpeaker, [IPresentationType::LightingTalks], true, $excluded_tracks) ;
            if($presentations) {
                foreach ($presentations as $speaker_presentation) {
                    $list[] = $speaker_presentation;
                }
            }
        }

        return $list;
    }

    /**
     * @param Summit $summit
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @param bool $published_ones
     * @return bool
     */
    public function hasAlternatePresentations
    (
        Summit $summit,
        $role                  = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles     = false,
        array $excluded_tracks = [],
        $published_ones = false
    )
    {
        return count($this->getAlternatePresentations($summit, $role, $include_sub_roles, $excluded_tracks, $published_ones)) > 0;
    }

    /**
     * @param Summit $summit
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @param bool $published_ones
     * @return array
     */
    public function getAlternatePresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = [],
        $published_ones = false
    )
    {
        $alternate_presentations = [];

        $exclude_category_dql = '';
        if(count($excluded_tracks) > 0){
            $exclude_category_dql = ' AND p.category NOT IN (:exclude_tracks)';
        }

        if($role == PresentationSpeaker::RoleSpeaker) {
            $query = $this->createQuery("SELECT p from models\summit\Presentation p 
            JOIN p.summit s
            JOIN p.speakers sp 
            WHERE s.id = :summit_id 
            AND p.published = :published
            AND sp.id = :speaker_id".$exclude_category_dql);
        }
        else{
            $query = $this->createQuery("SELECT p from models\summit\Presentation p 
            JOIN p.summit s
            JOIN p.moderator m 
            WHERE s.id = :summit_id 
            AND p.published = :published
            AND m.id = :speaker_id".$exclude_category_dql);
        }

        $query
            ->setParameter('summit_id', $summit->getId())
            ->setParameter('speaker_id', $this->id)
            ->setParameter('published', $published_ones ? 1 : 0);

        if(count($excluded_tracks) > 0){
            $query->setParameter('exclude_tracks', $excluded_tracks);
        }

        $presentations = $query->getResult();

        foreach ($presentations as $p) {
            if ($p->getSelectionStatus() == Presentation::SelectionStatus_Alternate) {
                $alternate_presentations[] = $p;
            }
        }

        // if role is moderator, add also the ones that belongs to role speaker ( if $include_sub_roles is true)
        if($include_sub_roles && $role == PresentationSpeaker::RoleModerator){
            $presentations = $this->getAlternatePresentations($summit,PresentationSpeaker::RoleSpeaker, $include_sub_roles, $excluded_tracks);
            if($presentations) {
                foreach ($presentations as $speaker_presentation)
                    $alternate_presentations[] = $speaker_presentation;
            }
        }

        return $alternate_presentations;
    }

    /**
     * @param Summit $summit,
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @return bool
     */
    public function hasRejectedPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = []
    )
    {
        return count($this->getRejectedPresentations($summit, $role, $include_sub_roles, $excluded_tracks)) > 0;
    }

    /**
     * @param Summit $summit,
     * @param string $role
     * @param bool $include_sub_roles
     * @param array $excluded_tracks
     * @return array
     */
    public function getRejectedPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $include_sub_roles = false,
        array $excluded_tracks = []
    ){
        $list = $this->getUnacceptedPresentations($summit, $role, true, $excluded_tracks);
        if($include_sub_roles && $role == PresentationSpeaker::RoleModerator){
            $presentations = $this->getUnacceptedPresentations($summit, PresentationSpeaker::RoleSpeaker, true, $excluded_tracks);
            if($presentations) {
                foreach ($presentations as $speaker_presentation) {
                    $list[] = $speaker_presentation;
                }
            }
        }
        return $list;
    }

    /**
     * @param Summit $summit,
     * @param string $role
     * @param bool $exclude_privates_tracks
     * @param array $excluded_tracks
     * @return array
     */
    public function getUnacceptedPresentations
    (
        Summit $summit,
        $role = PresentationSpeaker::RoleSpeaker,
        $exclude_privates_tracks = true,
        array $excluded_tracks = []
    )
    {
        $unaccepted_presentations = [];
        $private_tracks           = [];

        if($exclude_privates_tracks){
            $private_track_groups = $this->createQuery("SELECT pg from models\summit\PrivatePresentationCategoryGroup pg 
            JOIN pg.summit s
            WHERE s.id = :summit_id")
                ->setParameter('summit_id', $summit->getId())
                ->getResult();

            foreach($private_track_groups as $private_track_group){
                $current_private_tracks = $private_track_group->getCategories();
                if(count($current_private_tracks) == 0) continue;
                $private_tracks = array_merge($private_tracks, array_values($current_private_tracks));
            }
        }

        if(count($private_tracks) > 0) {
            $excluded_tracks = array_merge($excluded_tracks, $private_tracks);
        }

        $exclude_category_dql = '';
        if(count($excluded_tracks) > 0){
            $exclude_category_dql = ' AND p.category NOT IN (:exclude_tracks)';
        }

        if($role == PresentationSpeaker::RoleSpeaker) {
            $query = $this->createQuery("SELECT p from models\summit\Presentation p 
            JOIN p.summit s
            JOIN p.speakers sp 
            WHERE s.id = :summit_id 
            AND p.published = 0
            AND sp.id = :speaker_id".$exclude_category_dql);
        }
        else{
            $query = $this->createQuery("SELECT p from models\summit\Presentation p 
            JOIN p.summit s
            JOIN p.moderator m 
            WHERE s.id = :summit_id 
            AND p.published = 0
            AND m.id = :speaker_id".$exclude_category_dql);
        }

        $query
            ->setParameter('summit_id', $summit->getId())
            ->setParameter('speaker_id', $this->id);

        if(count($excluded_tracks) > 0){
            $query->setParameter('exclude_tracks', $excluded_tracks);
        }
        $presentations = $query->getResult();

        foreach ($presentations as $p) {
            if ($p->getSelectionStatus() == Presentation::SelectionStatus_Unaccepted) {
                $unaccepted_presentations[] = $p;
            }
        }

        return $unaccepted_presentations;
    }


    /**
     * @param Summit $summit
     * @param string $role
     * @param array $types_slugs
     * @param bool $exclude_privates_tracks
     * @param array $excluded_tracks
     * @return array
     */
    public function getPublishedPresentationsByType
    (
        Summit $summit,
        $role                    = PresentationSpeaker::RoleSpeaker,
        array $types_slugs       = [IPresentationType::Keynotes, IPresentationType::Panel, IPresentationType::Presentation, IPresentationType::LightingTalks],
        $exclude_privates_tracks = true,
        array $excluded_tracks   = []
    )
    {
        $query = $this->createQuery("SELECT pt from models\summit\PresentationType pt JOIN pt.summit s 
        WHERE s.id = :summit_id and pt.type IN (:types) ");
        $types = $query
            ->setParameter('summit_id', $summit->getIdentifier())
            ->setParameter('types', $types_slugs)
            ->getResult();

        if(count($types) == 0 ) return [];

        $private_tracks          = [];
        $exclude_privates_tracks = boolval($exclude_privates_tracks);

        if($exclude_privates_tracks){

            $query = $this->createQuery("SELECT ppcg from models\summit\PrivatePresentationCategoryGroup ppcg JOIN ppcg.summit s 
            WHERE s.id = :summit_id");
            $private_track_groups = $query
                ->setParameter('summit_id', $summit->getIdentifier())
                ->getResult();

            foreach($private_track_groups as $private_track_group){
                $current_private_tracks = $private_track_group->getCategories();
                if($current_private_tracks->count() == 0) continue;
                $private_tracks = array_merge($private_tracks, array_values($current_private_tracks));
            }
        }

        if(count($private_tracks) > 0) {
            $excluded_tracks = array_merge($excluded_tracks, $private_tracks);
        }

        $exclude_category_dql = '';
        if(count($excluded_tracks) > 0){
            $exclude_category_dql = ' and p.category NOT IN (:exclude_tracks)';
        }

        if($role == PresentationSpeaker::RoleSpeaker) {
            $query = $this->createQuery("SELECT p from models\summit\Presentation p 
            JOIN p.summit s
            JOIN p.speakers sp 
            WHERE s.id = :summit_id 
            and sp.id = :speaker_id
            and p.published = 1 and p.type IN (:types)".$exclude_category_dql);
        }
        else{
            $query = $this->createQuery("SELECT p from models\summit\Presentation p 
            JOIN p.summit s
            JOIN p.moderator m 
            WHERE s.id = :summit_id 
            and m.id = :speaker_id
            and p.published = 1 and p.type IN (:types)".$exclude_category_dql);
        }

        $query
            ->setParameter('summit_id', $summit->getId())
            ->setParameter('types', $types)
            ->setParameter('speaker_id', $this->id);

        if(count($excluded_tracks) > 0){
            $query->setParameter('exclude_tracks', $excluded_tracks);
        }

        return $query->getResult();
    }


    /**
     * @param null|int $summit_id
     * @param bool|true $published_ones
     * @return Presentation[]
     */
    public function moderated_presentations($summit_id, $published_ones = true)
    {

        return $this->moderated_presentations
            ->filter(function($p) use($published_ones, $summit_id){
                $res = $published_ones? $p->isPublished(): true;
                $res &= is_null($summit_id)? true : $p->getSummit()->getId() == $summit_id;
                return $res;
            });
    }

    /**
     * @param int $presentation_id
     * @return Presentation
     */
    public function getPresentation($presentation_id)
    {
        return $this->presentations->get($presentation_id);
    }

    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getPresentationIds($summit_id, $published_ones = true)
    {
        return $this->presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param bool|true $published_ones
     * @return array
     */
    public function getAllPresentationIds($published_ones = true)
    {
        return $this->presentations(null, $published_ones)->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getPresentations($summit_id, $published_ones = true)
    {
        return $this->presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity;
        })->toArray();
    }

    /**
     * @param bool|true $published_ones
     * @return array
     */
    public function getAllPresentations($published_ones = true)
    {
        return $this->presentations(null, $published_ones)->map(function($entity)  {
            return $entity;
        })->toArray();
    }


    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getModeratedPresentationIds($summit_id, $published_ones = true)
    {
        return $this->moderated_presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param bool|true $published_ones
     * @return array
     */
    public function getAllModeratedPresentationIds($published_ones = true)
    {
        return $this->moderated_presentations(null, $published_ones)->map(function($entity)  {
            return $entity->getId();
        })->toArray();
    }

    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getModeratedPresentations($summit_id, $published_ones = true)
    {
        return $this->moderated_presentations($summit_id, $published_ones)->map(function($entity)  {
            return $entity;
        })->toArray();
    }

    /**
     * @param bool|true $published_ones
     * @return array
     */
    public function getAllModeratedPresentations($published_ones = true)
    {
        return $this->moderated_presentations(null, $published_ones)->map(function($entity)  {
            return $entity;
        })->toArray();
    }

    /**
     * @return File
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param File $photo
     */
    public function setPhoto(File $photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember(Member $member){
        $this->member = $member;
    }

    /**
     * @return bool
     */
    public function hasMember(){
        return $this->getMemberId() > 0;
    }

    /**
     * @return int
     */
    public function getMemberId()
    {
        try{
            if(is_null($this->member)) return 0;
            return $this->member->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return SpeakerRegistrationRequest
     */
    public function getRegistrationRequest()
    {
        return $this->registration_request;
    }

    /**
     * @param SpeakerRegistrationRequest $registration_request
     */
    public function setRegistrationRequest($registration_request)
    {
        $this->registration_request = $registration_request;
        $registration_request->setSpeaker($this);
    }

    /**
     * @return string
     */
    public function getFullName(){
        $fullname = $this->first_name;
        if(!empty($this->last_name)){
            if(!empty($fullname)) $fullname .= ', ';
            $fullname .= $this->last_name;
        }
        return $fullname;
    }

    /**
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest[]
     */
    public function getSummitAssistances()
    {
        return $this->summit_assistances;
    }

    /**
     * @param PresentationSpeakerSummitAssistanceConfirmationRequest $assistance
     * @return $this
     */
    public function addSummitAssistance(PresentationSpeakerSummitAssistanceConfirmationRequest $assistance){
        $this->summit_assistances->add($assistance);
        $assistance->setSpeaker($this);
        return $this;
    }

    /**
     * @param Summit $summit
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function getAssistanceFor(Summit $summit)
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('summit', $summit));
        $res      = $this->summit_assistances->matching($criteria)->first();
        return $res === false ? null : $res;
    }

    /**
     * @param Summit $summit
     * @return bool
     */
    public function hasAssistanceFor(Summit $summit){
        return $this->getAssistanceFor($summit) != null;
    }

    /**
     * @return mixed
     */
    public function getCreatedFromApi()
    {
        return $this->created_from_api;
    }

    /**
     * @param mixed $created_from_api
     */
    public function setCreatedFromApi($created_from_api)
    {
        $this->created_from_api = $created_from_api;
    }

    /**
     * @param Summit $summit
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function buildAssistanceFor(Summit $summit)
    {
        $request = new PresentationSpeakerSummitAssistanceConfirmationRequest;
        $request->setSummit($summit);
        $request->setSpeaker($this);
        return $request;
    }

    /**
     * @param Summit $summit
     * @return bool
     */
    public function isSpeakerOfSummit(Summit $summit){

        $query = <<<SQL
SELECT DISTINCT Summit.* FROM SummitEvent 
INNER JOIN Summit ON Summit.ID = SummitEvent.SummitID
INNER JOIN Presentation ON Presentation.ID = SummitEvent.ID
WHERE
SummitEvent.Published = 1
AND (
	EXISTS ( 
		SELECT Presentation_Speakers.ID FROM Presentation_Speakers 
		WHERE Presentation_Speakers.PresentationID = Presentation.ID AND
		Presentation_Speakers.PresentationSpeakerID = :speaker_id
	) OR
    Presentation.ModeratorID = :speaker_id
)
AND Summit.ID = :summit_id;
SQL;

        $rsm = new ResultSetMappingBuilder($this->getEM());
        $rsm->addRootEntityFromClassMetadata(\models\summit\Summit::class, 's');

        // build rsm here
        $native_query = $this->getEM()->createNativeQuery($query, $rsm);


        $native_query->setParameter("speaker_id", $this->id);
        $native_query->setParameter("summit_id", $summit->getId());

        $summits = $native_query->getResult();

        return count($summits) > 0;
    }

    /**
     * @return Summit[]
     */
    public function getRelatedSummits(){

        $query = <<<SQL
SELECT DISTINCT Summit.* FROM Presentation_Speakers 
INNER JOIN Presentation ON Presentation.ID = Presentation_Speakers.PresentationID
INNER JOIN SummitEvent ON SummitEvent.ID = Presentation.ID
INNER JOIN Summit ON Summit.ID = SummitEvent.SummitID
WHERE SummitEvent.Published = 1 AND 
( Presentation_Speakers.PresentationSpeakerID = :speaker_id OR  Presentation.ModeratorID = :speaker_id )
SQL;

        $rsm = new ResultSetMappingBuilder($this->getEM());
        $rsm->addRootEntityFromClassMetadata(\models\summit\Summit::class, 's');

        // build rsm here
        $native_query = $this->getEM()->createNativeQuery($query, $rsm);

        $native_query->setParameter("speaker_id", $this->id);

        $summits = $native_query->getResult();
        if(count($summits) == 0){
            $assistance = $this->getLatestAssistance();
            if(!$assistance) return [];
            return [ $assistance->getSummit() ];
        }
        return $summits;
    }


    /**
     * @return null|string
     */
    public function getEmail(){
        if($this->hasMember()){
            return $this->member->getEmail();
        }
        if($this->hasRegistrationRequest()){
            return $this->registration_request->getEmail();
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasRegistrationRequest(){
        return $this->getRegistrationRequestId() > 0;
    }

    /**
     * @return int
     */
    public function getRegistrationRequestId()
    {
        try{
            if(is_null($this->registration_request)) return 0;
            return $this->registration_request->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return PresentationSpeakerSummitAssistanceConfirmationRequest
     */
    public function getLatestAssistance(){
        return $this->summit_assistances->last();
    }

    // life cycle events

    /**
     * @var PreRemoveEventArgs
     */
    private $pre_remove_events;
    /**
     * @ORM\PreRemove:
     */
    public function deleting($args){
        $this->pre_remove_events = new PreRemoveEventArgs
        (
            [
                'id'         => $this->id,
                'class_name' => "PresentationSpeaker",
                'summits'    => $this->getRelatedSummits(),
            ]
        );
    }

    /**
     * @ORM\PostRemove:
     */
    public function deleted($args){

        Event::fire(new PresentationSpeakerDeleted($this,  $this->pre_remove_events));
        $this->pre_remove_events = null;
    }

    /**
     * @var PreUpdateEventArgs
     */
    private $pre_update_args;

    /**
     * @ORM\PreUpdate:
     */
    public function updating(PreUpdateEventArgs $args){
        $this->pre_update_args = $args;
    }

    /**
     * @ORM\PostUpdate:
     */
    public function updated($args)
    {
        Event::fire(new PresentationSpeakerUpdated($this, $this->pre_update_args));
        $this->pre_update_args = null;
    }

    /**
     * @ORM\PostPersist
     */
    public function inserted($args){
        Event::fire(new PresentationSpeakerCreated($this, $args));
    }

    /**
     * @return bool
     */
    public function isAvailableForBureau()
    {
        return $this->available_for_bureau;
    }

    /**
     * @param bool $available_for_bureau
     */
    public function setAvailableForBureau($available_for_bureau)
    {
        $this->available_for_bureau = $available_for_bureau;
    }

    /**
     * @return bool
     */
    public function isFundedTravel()
    {
        return $this->funded_travel;
    }

    /**
     * @param bool $funded_travel
     */
    public function setFundedTravel($funded_travel)
    {
        $this->funded_travel = $funded_travel;
    }

    /**
     * @return bool
     */
    public function isWillingToTravel()
    {
        return $this->willing_to_travel;
    }

    /**
     * @param bool $willing_to_travel
     */
    public function setWillingToTravel($willing_to_travel)
    {
        $this->willing_to_travel = $willing_to_travel;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return bool
     */
    public function isWillingToPresentVideo()
    {
        return $this->willing_to_present_video;
    }

    /**
     * @param bool $willing_to_present_video
     */
    public function setWillingToPresentVideo($willing_to_present_video)
    {
        $this->willing_to_present_video = $willing_to_present_video;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return bool
     */
    public function isOrgHasCloud()
    {
        return $this->org_has_cloud;
    }

    /**
     * @param bool $org_has_cloud
     */
    public function setOrgHasCloud($org_has_cloud)
    {
        $this->org_has_cloud = $org_has_cloud;
    }

    /**
     * @return SpeakerExpertise[]
     */
    public function getAreasOfExpertise()
    {
        return $this->areas_of_expertise;
    }

    /**
     * @param SpeakerExpertise $area_of_expertise
     */
    public function addAreaOfExpertise(SpeakerExpertise $area_of_expertise){
        $this->areas_of_expertise->add($area_of_expertise);
        $area_of_expertise->setSpeaker($this);
    }

    /**
     * @return SpeakerPresentationLink[]
     */
    public function getOtherPresentationLinks()
    {
        return $this->other_presentation_links;
    }

    /**
     * @param SpeakerPresentationLink $link
     */
    public function addOtherPresentationLink(SpeakerPresentationLink $link){
        $this->other_presentation_links->add($link);
        $link->setSpeaker($this);
    }

    /**
     * @return SpeakerTravelPreference[]
     */
    public function getTravelPreferences()
    {
        return $this->travel_preferences;
    }

    /**
     * @param SpeakerTravelPreference $travel_preference
     */
    public function addTravelPreference(SpeakerTravelPreference $travel_preference){
        $this->travel_preferences->add($travel_preference);
        $travel_preference->setSpeaker($this);
    }

    /**
     * @return SpeakerLanguage[]
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param SpeakerLanguage $language
     */
    public function addLanguage(SpeakerLanguage $language){
        $this->languages->add($language);
        $language->setSpeaker($this);
    }

    /**
     * @return SpeakerOrganizationalRole[]
     */
    public function getOrganizationalRoles()
    {
        return $this->organizational_roles;
    }

    public function clearOrganizationalRoles(){
        $this->organizational_roles->clear();
    }

    public function addOrganizationalRole(SpeakerOrganizationalRole $role){
        $this->organizational_roles->add($role);
    }

    /**
     * @return SpeakerActiveInvolvement[]
     */
    public function getActiveInvolvements()
    {
        return $this->active_involvements;
    }

    public function clearActiveInvolvements(){
        $this->active_involvements->clear();
    }

    /**
     * @param SpeakerActiveInvolvement $active_involvement
     */
    public function addActiveInvolvement(SpeakerActiveInvolvement $active_involvement){
        $this->active_involvements->add($active_involvement);
    }

    /**
     * @param Presentation $presentation
     */
    public function addModeratedPresentation(Presentation $presentation){
        $this->moderated_presentations->add($presentation);
        $presentation->setModerator($this);
    }

    /**
     * @param Summit $summit
     * @return bool
     */
    public function isModeratorFor(Summit $summit){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('summit', $summit));
        return $this->moderated_presentations->matching($criteria)->count() > 0;
    }

    /**
     * @param Summit $summit
     * @return bool
     */
    public function announcementEmailAlreadySent(Summit $summit)
    {
        $email_type = $this->getAnnouncementEmailTypeSent($summit);
        return !is_null($email_type) && $email_type !== SpeakerAnnouncementSummitEmail::TypeNone;
    }

    /**
     * @param Summit $summit
     * @return string|null
     */
    public function getAnnouncementEmailTypeSent(Summit $summit)
    {
        $criteria = Criteria::create();

        $criteria
            ->where(Criteria::expr()->eq('summit', $summit))
            ->andWhere(Criteria::expr()->notIn('type', [
                SpeakerAnnouncementSummitEmail::TypeCreateMembership,
                SpeakerAnnouncementSummitEmail::TypeSecondBreakoutRegister,
                SpeakerAnnouncementSummitEmail::TypeSecondBreakoutReminder,
            ]));

        $email = $this->announcement_summit_emails->matching($criteria)->first();

        return $email ? $email->getType() : null;
    }
}