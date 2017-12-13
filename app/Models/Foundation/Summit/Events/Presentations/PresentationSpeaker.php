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
     * @ORM\ManyToOne(targetEntity="SpeakerRegistrationRequest", cascade={"persist"})
     * @ORM\JoinColumn(name="RegistrationRequestID", referencedColumnName="ID")
     * @var SpeakerRegistrationRequest
     */
    private $registration_request;

    /**
     * @ORM\OneToMany(targetEntity="PresentationSpeakerSummitAssistanceConfirmationRequest", mappedBy="speaker", cascade={"persist"})
     * @var PresentationSpeakerSummitAssistanceConfirmationRequest[]
     */
    private $summit_assistances;

    /**
     * @ORM\OneToMany(targetEntity="SpeakerSummitRegistrationPromoCode", mappedBy="speaker", cascade={"persist"})
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
     * @return mixed
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

        $this->presentations           = new ArrayCollection;
        $this->moderated_presentations = new ArrayCollection;
        $this->summit_assistances      = new ArrayCollection;
        $this->promo_codes             = new ArrayCollection;
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
     * @param Summit $summit
     * @return SpeakerSummitRegistrationPromoCode
     */
    public function getPromoCodeFor(Summit $summit){
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('summit', $summit));
        return $this->promo_codes->matching($criteria)->first();
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
        return $this->summit_assistances->matching($criteria)->first();
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
     * @return Summit[]
     */
    public function getRelatedSummits(){

        $query = <<<SQL
SELECT DISTINCT Summit.* FROM Presentation_Speakers 
INNER JOIN Presentation ON Presentation.ID = Presentation_Speakers.PresentationID
INNER JOIN SummitEvent ON SummitEvent.ID = Presentation.ID
INNER JOIN Summit ON Summit.ID = SummitEvent.SummitID
WHERE SummitEvent.Published = 1 AND Presentation_Speakers.PresentationSpeakerID = :speaker_id; 
SQL;

        $rsm = new ResultSetMappingBuilder($this->getEM());
        $rsm->addRootEntityFromClassMetadata(\models\summit\Summit::class, 's');

        // build rsm here
        $native_query = $this->getEM()->createNativeQuery($query, $rsm);

        $native_query->setParameter("speaker_id", $this->id);

        $summits = $native_query->getResult();
        if(count($summits) == 0){
            $assistance = $this->getLatestAssistance();
            if(is_null($assistance)) return [];
            return [ $assistance->getSummit() ];
        }
        return $summits;
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
}