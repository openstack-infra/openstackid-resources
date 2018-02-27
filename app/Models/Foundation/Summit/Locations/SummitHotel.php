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

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitHotel")
 * Class SummitHotel
 * @package models\summit
 */
class SummitHotel extends SummitExternalLocation
{
    /**
     * @return string
     */
    public function getClassName(){
        return self::ClassName;
    }

    const ClassName = 'SummitHotel';

    /**
     * @return string
     */
    public function getBookingLink()
    {
        return $this->booking_link;
    }

    /**
     * @param string $booking_link
     */
    public function setBookingLink($booking_link)
    {
        $this->booking_link = $booking_link;
    }
    /**
     * @ORM\Column(name="BookingLink", type="string")
     */
    private $booking_link;

    /**
     * @return bool
     */
    public function getSoldOut()
    {
        return $this->sold_out;
    }

    /**
     * @param bool $sold_out
     */
    public function setSoldOut($sold_out)
    {
        $this->sold_out = $sold_out;
    }

    /**
     * @return string
     */
    public function getHotelType()
    {
        return $this->hotel_type;
    }

    /**
     * @param string $hotel_type
     */
    public function setHotelType($hotel_type)
    {
        $this->hotel_type = $hotel_type;
    }

    /**
     * @ORM\Column(name="SoldOut", type="boolean")
     */
    private $sold_out;

    /**
     * @ORM\Column(name="Type", type="string")
     */
    private $hotel_type;

    public static $metadata = [
        'class_name'    => self::ClassName,
        'hotel_type'    => 'string',
        'sold_out'      => 'boolean',
        'booking_link'  => 'string',
    ];

    /**
     * @return array
     */
    public static function getMetadata(){
        return array_merge(SummitExternalLocation::getMetadata(), self::$metadata);
    }

}