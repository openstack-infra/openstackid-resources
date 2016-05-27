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
    protected static $array_mappings = array
    (
        'YouTubeID'    => 'youtube_id:json_text',
        'DateUploaded' => 'data_uploaded:datetime_epoch',
        'Highlighted'  => 'highlighted:json_boolean',
        'Views'        => 'highlighted:json_int',
    );

    /**
     * @ORM\Column(name="YouTubeID", type="string")
     * @var string
     */
    private $youtube_id;

    /**
     * @ORM\Column(name="DateUploaded", type="datetime")
     * @var string
     */
    private $date_uploaded;

    /**
     * @ORM\Column(name="Highlighted", type="boolean")
     * @var string
     */
    private $highlighted;

    /**
     * @ORM\Column(name="Views", type="integer")
     * @var string
     */
    private $views;
}