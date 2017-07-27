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

use App\Events\PresentationMaterialCreated;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
use Illuminate\Support\Facades\Event;

/**
 * @ORM\Entity
 * @ORM\Table(name="PresentationMaterial")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="ClassName", type="string")
 * @ORM\DiscriminatorMap({"PresentationSlide" = "PresentationSlide", "PresentationVideo" = "PresentationVideo", "PresentationLink" = "PresentationLink"})
 * @ORM\HasLifecycleCallbacks
 * Class PresentationMaterial
 * @package models\summit
 */
abstract class PresentationMaterial extends SilverstripeBaseModel
{

    /**
     * @return string
     */
    public function getClassName(){
        return 'PresentationMaterial';
    }

    /**
    * @ORM\ManyToOne(targetEntity="models\summit\Presentation", inversedBy="materials")
    * @ORM\JoinColumn(name="PresentationID", referencedColumnName="ID")
    * @var Presentation
    */
    protected $presentation;

    /**
     * @return Presentation
     */
    public function getPresentation(){
        return $this->presentation;
    }

    /**
     * @param Presentation $presentation
     */
    public function setPresentation(Presentation $presentation){
        $this->presentation = $presentation;
    }


    public function unsetPresentation(){
        $this->presentation = null;
    }

    /**
     * @return int
     */
    public function getPresentationId(){
        try {
            return $this->presentation->getId();
        }
        catch (\Exception $ex){
            return 0;
        }
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
    public function getDisplayOnSite()
    {
        return $this->display_on_site;
    }

    /**
     * @param string $display_on_site
     */
    public function setDisplayOnSite($display_on_site)
    {
        $this->display_on_site = $display_on_site;
    }

    /**
     * @return string
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * @param string $featured
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;
    }

    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="Description", type="string")
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(name="DisplayOnSite", type="boolean")
     * @var string
     */
    protected $display_on_site;

    /**
     * @ORM\Column(name="`Order`", type="integer")
     * @var int
     */
    protected $order;

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
     * @ORM\Column(name="Featured", type="boolean")
     * @var string
     */
    protected $featured;

    public function __construct()
    {
        parent::__construct();
        $this->featured        = false;
        $this->display_on_site = false;
        $this->order           = 0;
    }

    /**
     * @ORM\PostPersist
     */
    public function inserted($args){
        Event::fire(new PresentationMaterialCreated($this, $args));
    }
}