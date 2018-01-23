<?php namespace models\summit;
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
use Doctrine\ORM\Mapping AS ORM;
use models\main\Member;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitRegistrationPromoCodeRepository")
 * @ORM\Table(name="SummitRegistrationPromoCode")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitRegistrationPromoCode" = "SummitRegistrationPromoCode",
 *     "SpeakerSummitRegistrationPromoCode" = "SpeakerSummitRegistrationPromoCode",
 *     "MemberSummitRegistrationPromoCode" = "MemberSummitRegistrationPromoCode",
 *     "SponsorSummitRegistrationPromoCode" = "SponsorSummitRegistrationPromoCode"})
 * Class SummitRegistrationPromoCode
 * @package models\summit
 */
class SummitRegistrationPromoCode extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Code", type="string")
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(name="EmailSent", type="boolean")
     * @var boolean
     */
    protected $email_sent;

    /**
     * @ORM\Column(name="Redeemed", type="boolean")
     * @var boolean
     */
    protected $redeemed;

    /**
     * @ORM\Column(name="Source", type="string")
     * @var string
     */
    protected $source;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\Summit", inversedBy="promo_codes")
     * @ORM\JoinColumn(name="SummitID", referencedColumnName="ID")
     * @var Summit
     */
    protected $summit;

    public function setSummit($summit){
        $this->summit = $summit;
    }

    /**
     * @return Summit
     */
    public function getSummit(){
        return $this->summit;
    }

    /**
     * @return int
     */
    public function getSummitId(){
        try {
            return $this->summit->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="CreatorID", referencedColumnName="ID")
     * @var Member
     */
    protected $creator;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return bool
     */
    public function isEmailSent()
    {
        return $this->email_sent;
    }

    /**
     * @param bool $email_sent
     */
    public function setEmailSent($email_sent)
    {
        $this->email_sent = $email_sent;
    }

    /**
     * @return bool
     */
    public function isRedeemed()
    {
        return $this->redeemed;
    }

    /**
     * @param bool $redeemed
     */
    public function setRedeemed($redeemed)
    {
        $this->redeemed = $redeemed;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return Member
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param Member $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    public function __construct()
    {
        $this->email_sent = false;
        $this->redeemed   = false;
        parent::__construct();
    }

    public function setSourceAdmin(){
        $this->source = 'ADMIN';
    }

    /**
     * @return int
     */
    public function getCreatorId(){
        try {
            return is_null($this->creator) ? 0: $this->creator->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasCreator(){
        return $this->getCreatorId() > 0;
    }

    const ClassName = 'SUMMIT_PROMO_CODE';

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    public static $metadata = [
        'code'       => 'string',
        'email_sent' => 'boolean',
        'redeemed'   => 'boolean',
        'source'     => ['CSV','ADMIN'],
        'summit_id'  => 'integer',
        'creator_id' => 'integer',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return self::$metadata;
    }
}
