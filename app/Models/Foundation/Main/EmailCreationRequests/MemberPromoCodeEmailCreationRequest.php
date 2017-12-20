<?php namespace models\main;
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
use models\summit\SummitRegistrationPromoCode;
/**
 * @ORM\Entity
 * @ORM\Table(name="MemberPromoCodeEmailCreationRequest")
 * Class MemberPromoCodeEmailCreationRequest
 * @package models\main
 */
class MemberPromoCodeEmailCreationRequest extends EmailCreationRequest
{
    /**
     * @ORM\ManyToOne(targetEntity="models\summit\SummitRegistrationPromoCode")
     * @ORM\JoinColumn(name="PromoCodeID", referencedColumnName="ID")
     * @var SummitRegistrationPromoCode
     */
    protected $promo_code;

    /**
     * @ORM\Column(name="Email", type="string")
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    protected $name;

    public function __construct()
    {
        $this->template_name = "member-promo-code";
        parent::__construct();
    }

    /**
     * @return SummitRegistrationPromoCode
     */
    public function getPromoCode()
    {
        return $this->promo_code;
    }

    /**
     * @param SummitRegistrationPromoCode $promo_code
     */
    public function setPromoCode($promo_code)
    {
        $this->promo_code = $promo_code;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}