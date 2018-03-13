<?php namespace App\Models\Foundation\Summit\Events\RSVP;
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
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Table(name="RSVPQuestionValueTemplate")
 * @ORM\Entity
 * Class RSVPQuestionValueTemplate
 * @package App\Models\Foundation\Summit\Events\RSVP
 */
class RSVPQuestionValueTemplate extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Value", type="string")
     * @var string
     */
    private $value;

    /**
     * @ORM\Column(name="Label", type="string")
     * @var string
     */
    private $label;

    /**
     * @ORM\Column(name="`Order`", type="integer")
     * @var int
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="RSVPMultiValueQuestionTemplate", inversedBy="values")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var RSVPMultiValueQuestionTemplate
     */
    private $owner;

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return RSVPMultiValueQuestionTemplate
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param RSVPMultiValueQuestionTemplate $owner
     */
    public function setOwner(RSVPMultiValueQuestionTemplate $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getOwnerId(){
        try{
            return is_null($this->owner) ? 0 : $this->owner->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
    }
}