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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SummitGeoLocatedLocation")
 * Class SummitGeoLocatedLocation
 * @package models\summit
 */
class SummitGeoLocatedLocation extends SummitAbstractLocation
{
    /**
     * @return string
     */
    public function getClassName(){
        return 'SummitGeoLocatedLocation';
    }


    /**
     * @ORM\Column(name="Address1", type="string")
     */
    protected $address1;

    /**
     * @return mixed
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param mixed $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return mixed
     */
    public function getZipCode()
    {
        return $this->zip_code;
    }

    /**
     * @param mixed $zip_code
     */
    public function setZipCode($zip_code)
    {
        $this->zip_code = $zip_code;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getWebsiteUrl()
    {
        return $this->website_url;
    }

    /**
     * @param mixed $website_url
     */
    public function setWebsiteUrl($website_url)
    {
        $this->website_url = $website_url;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return mixed
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param mixed $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * @return mixed
     */
    public function getDisplayOnSite()
    {
        return $this->display_on_site;
    }

    /**
     * @param mixed $display_on_site
     */
    public function setDisplayOnSite($display_on_site)
    {
        $this->display_on_site = $display_on_site;
    }

    /**
     * @return mixed
     */
    public function getDetailsPage()
    {
        return $this->details_page;
    }

    /**
     * @param mixed $details_page
     */
    public function setDetailsPage($details_page)
    {
        $this->details_page = $details_page;
    }

    /**
     * @return mixed
     */
    public function getLocationMessage()
    {
        return $this->location_message;
    }

    /**
     * @param mixed $location_message
     */
    public function setLocationMessage($location_message)
    {
        $this->location_message = $location_message;
    }

    /**
     * @ORM\Column(name="Address2", type="string")
     */
    protected $address2;

    /**
     * @ORM\Column(name="ZipCode", type="string")
     */
    protected $zip_code;

    /**
     * @ORM\Column(name="City", type="string")
     */
    protected $city;

    /**
     * @ORM\Column(name="State", type="string")
     */
    protected $state;

    /**
     * @ORM\Column(name="Country", type="string")
     */
    protected $country;

    /**
     * @ORM\Column(name="WebSiteUrl", type="string")
     */
    protected $website_url;

    /**
     * @ORM\Column(name="Lng", type="string")
     */
    protected $lng;

    /**
     * @ORM\Column(name="Lat", type="string")
     */
    protected $lat;

    /**
     * @ORM\Column(name="DisplayOnSite", type="boolean")
     */
    protected $display_on_site;

    /**
     * @ORM\Column(name="DetailsPage", type="boolean")
     */
    protected $details_page;

    /**
     * @ORM\Column(name="LocationMessage", type="string")
     */
    protected $location_message;

    /**
     * @ORM\OneToMany(targetEntity="models\summit\SummitLocationImage", mappedBy="location", cascade={"persist"})
     * @var SummitLocationImage[]
     */
    protected $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @return SummitLocationImage[]
     */
    public function getMaps()
    {
        return $this->images
            ->matching
            (
                Criteria::create()
                ->where(Criteria::expr()->eq("class_name", "SummitLocationMap"))
                ->orderBy(array("order" => Criteria::ASC))
            );
    }

    /**
     * @return SummitLocationImage[]
     */
    public function getImages()
    {

        return $this->images
            ->matching
            (
                Criteria::create()
                    ->where(Criteria::expr()->eq("class_name", "SummitLocationImage"))
                    ->orderBy(array("order" => Criteria::ASC))
            );
    }

    /**
     * @param int $image_id
     * @return SummitLocationImage
     */
    public function getImage($image_id){
        return $this->images
            ->matching
            (
                Criteria::create()->where(Criteria::expr()->eq("id", $image_id))

            )->first();
    }
}