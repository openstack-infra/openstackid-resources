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
use models\main\File;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="PresentationSlide")
 * Class PresentationSlide
 * @package models\summit
 */
class PresentationSlide extends PresentationMaterial
{

    /**
     * @return string
     */
    public function getClassName(){
        return 'PresentationSlide';
    }

    /**
     * @ORM\Column(name="Link", type="string")
     * @var string
     */
    private $link;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File", cascade={"persist"})
     * @ORM\JoinColumn(name="SlideID", referencedColumnName="ID")
     * @var File
     */
    private $slide;

    /**
     * @return File
     */
    public function getSlide()
    {
        return $this->slide;
    }

    /**
     * @param File $slide
     */
    public function setSlide($slide)
    {
        $this->slide = $slide;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return bool
     */
    public function hasSlide(){
        return $this->getSlideId() > 0;
    }

    /**
     * @return int
     */
    public function getSlideId(){
        try{
            return !is_null($this->slide) ? $this->slide->getId():0;
        }
        catch(\Exception $ex){
            return 0;
        }
    }
}