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
 * @see https://github.com/doctrine/doctrine2/commit/ff28507b88ffd98830c44762
 * //@ORM\AssociationOverrides({
 * //     @ORM\AssociationOverride(
 * //         name="summit",
 * //         inversedBy="locations"
 * //     )
 * // })
 */

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitAbstractLocation")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"SummitAbstractLocation" = "SummitAbstractLocation", "SummitGeoLocatedLocation" = "SummitGeoLocatedLocation", "SummitExternalLocation" = "SummitExternalLocation", "SummitVenue" = "SummitVenue", "SummitHotel" = "SummitHotel", "SummitAirport" = "SummitAirport", "SummitVenueRoom" = "SummitVenueRoom"})
 * Class SummitAbstractLocation
 * @package models\summit
 */
class SummitAbstractLocation extends SilverstripeBaseModel
{
    const TypeExternal = 'External';
    const TypeInternal = 'Internal';
    const TypeNone     = 'None';

    public function __construct()
    {
        parent::__construct();
        $this->type = self::TypeNone;
    }

    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitAbstractLocation';
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getLocationType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setLocationType($type)
    {
        $this->type = $type;
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

    use SummitOwned;


    /**
     * @ORM\Column(name="Name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="Description", type="string")
     */
    protected $description;


    /**
     * @ORM\Column(name="LocationType", type="string")
     */
    protected $type;

    /**
     * @ORM\Column(name="Order", type="integer")
     */
    protected $order;


}