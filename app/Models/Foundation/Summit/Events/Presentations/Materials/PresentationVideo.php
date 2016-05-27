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
 * @ORM\Table(name="PresentationVideo")
  * Class PresentationVideo
 * @package models\summit
 */
class PresentationVideo extends PresentationMaterial
{
    /**
     * @ORM\Column(name="YouTubeID", type="string")
     * @var string
     */
    private $youtube_id;

    /**
     * @return string
     */
    public function getYoutubeId()
    {
        return $this->youtube_id;
    }

    /**
     * @param string $youtube_id
     */
    public function setYoutubeId($youtube_id)
    {
        $this->youtube_id = $youtube_id;
    }

    /**
     * @return \DateTime
     */
    public function getDateUploaded()
    {
        return $this->date_uploaded;
    }

    /**
     * @param \DateTime $date_uploaded
     */
    public function setDateUploaded($date_uploaded)
    {
        $this->date_uploaded = $date_uploaded;
    }

    /**
     * @return bool
     */
    public function getHighlighted()
    {
        return (bool)$this->highlighted;
    }

    /**
     * @param bool $highlighted
     */
    public function setHighlighted($highlighted)
    {
        $this->highlighted = $highlighted;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * @ORM\Column(name="DateUploaded", type="datetime")
     * @var \DateTime
     */
    private $date_uploaded;

    /**
     * @ORM\Column(name="Highlighted", type="boolean")
     * @var bool
     */
    private $highlighted;

    /**
     * @ORM\Column(name="Views", type="integer")
     * @var int
     */
    private $views;

    public function __construct()
    {
        parent::__construct();
        $this->highlighted = false;
        $this->views       = 0;
        $this->date_uploaded = new \DateTime();
    }
}