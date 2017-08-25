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
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAttendeeTicket")
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSummitAttendeeTicketRepository")
 * Class SummitAttendeeTicket
 * @package models\summit
 */
class SummitAttendeeTicket extends SilverstripeBaseModel
{
    /**
     * @return mixed
     */
    public function getChangedDate()
    {
        return $this->changed_date;
    }

    /**
     * @param mixed $changed_date
     */
    public function setChangedDate($changed_date)
    {
        $this->changed_date = $changed_date;
    }
    /**
     * @return string
     */
    public function getExternalOrderId()
    {
        return $this->external_order_id;
    }

    /**
     * @param string $external_order_id
     */
    public function setExternalOrderId($external_order_id)
    {
        $this->external_order_id = $external_order_id;
    }

    /**
     * @return string
     */
    public function getExternalAttendeeId()
    {
        return $this->external_attendee_id;
    }

    /**
     * @param string $external_attendee_id
     */
    public function setExternalAttendeeId($external_attendee_id)
    {
        $this->external_attendee_id = $external_attendee_id;
    }

    /**
     * @return \DateTime
     */
    public function getBoughtDate()
    {
        return $this->bought_date;
    }

    /**
     * @param \DateTime $bought_date
     */
    public function setBoughtDate($bought_date)
    {
        $this->bought_date = $bought_date;
    }

    /**
     * @return SummitTicketType
     */
    public function getTicketType()
    {
        return $this->ticket_type;
    }

    /**
     * @return int
     */
    public function getTicketTypeId(){
        try{
            return $this->ticket_type->getId();
        }
        catch(\Exception $ex){
            return 0;
        }
    }

    /**
     * @return bool
     */
    public function hasTicketType(){
        return $this->getTicketTypeId() > 0;
    }

    /**
     * @param SummitTicketType $ticket_type
     */
    public function setTicketType($ticket_type)
    {
        $this->ticket_type = $ticket_type;
    }

    /**
     * @return SummitAttendee
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param SummitAttendee $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @ORM\Column(name="ExternalOrderId", type="string")
     * @var string
     */
    private $external_order_id;

    /**
     * @ORM\Column(name="ExternalAttendeeId", type="string")
     * @var
     */
    private $external_attendee_id;

    /**
     * @ORM\Column(name="TicketBoughtDate", type="datetime")
     * @var \DateTime
     */
    private $bought_date;

    /**
     * @ORM\Column(name="TicketChangedDate", type="datetime")
     * @var \DateTime
     */
    private $changed_date;


    /**
     * @ORM\ManyToOne(targetEntity="SummitTicketType", fetch="EAGER")
     * @ORM\JoinColumn(name="TicketTypeID", referencedColumnName="ID")
     * @var SummitTicketType
     */
    private $ticket_type;

    /**
     * @ORM\ManyToOne(targetEntity="SummitAttendee", inversedBy="tickets")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var SummitAttendee
     */
    private $owner;

}