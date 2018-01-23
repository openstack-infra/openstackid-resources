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

/**
 * @ORM\Entity
 * @ORM\Table(name="MemberSummitRegistrationPromoCode")
 * Class MemberSummitRegistrationPromoCode
 * @package models\summit
 */
class MemberSummitRegistrationPromoCode extends SummitRegistrationPromoCode
{

    public static $valid_type_values = ["VIP","ATC","MEDIA ANALYST","SPONSOR"];
    /**
     * @ORM\Column(name="FirstName", type="string")
     * @var string
     */
    protected $first_name;

    /**
     * @ORM\Column(name="LastName", type="string")
     * @var string
     */
    protected $last_name;

    /**
     * @ORM\Column(name="Email", type="string")
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    protected $owner;

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
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
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    const ClassName = 'MEMBER_PROMO_CODE';

    public static $metadata = [
        'class_name' => self::ClassName,
        'first_name' => 'string',
        'last_name'  => 'string',
        'email'      => 'string',
        'type'       => ['VIP','ATC','MEDIA ANALYST','SPONSOR'],
        'owner_id'   => 'integer'
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(SummitRegistrationPromoCode::getMetadata(), self::$metadata);
    }

    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    /**
     * @return int
     */
    public function getOwnerId(){
        try {
            return is_null($this->owner) ? 0: $this->owner->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasOwner(){
        return $this->getOwnerId() > 0;
    }

}