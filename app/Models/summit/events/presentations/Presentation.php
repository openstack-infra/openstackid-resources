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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Presentation
 * @ORM\Entity
 * @ORM\Table(name="Presentation")
 * @package models\summit
 */
class Presentation extends SummitEvent
{

    /**
     * @var PresentationMaterial[]
     * @ORM\OneToMany(targetEntity="models\summit\PresentationMaterial", mappedBy="presentation")
     */
    private $materials;

    /**
     * @var PresentationSpeaker[]
     * @ORM\ManyToMany(targetEntity="models\summit\PresentationSpeaker", inversedBy="presentations")
     * @ORM\JoinTable(name="Presentation_Speakers",
     *      joinColumns={@ORM\JoinColumn(name="PresentationID", referencedColumnName="ID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="PresentationSpeakerID", referencedColumnName="ID")}
     *      )
     */
    private $speakers;

    public function __construct()
    {
        $this->materials = new ArrayCollection();
        $this->speakers  = new ArrayCollection();
    }

    /**
     * @var bool
     */
    private $from_speaker;

    protected static $array_mappings = array
    (
        'Level'                   => 'level',
        'CategoryID'              => 'track_id:json_int',
        'ModeratorID'             => 'moderator_speaker_id:json_int',
        'ProblemAddressed'        => 'problem_addressed:json_string',
        'AttendeesExpectedLearnt' => 'attendees_expected_learnt:json_string',
        'SelectionMotive'         => 'selection_motive:json_string',
    );

    /**
     * @ORM\Column(name="Level", type="string")
     * @var string
     */
    private $level;

    /**
     * @ORM\Column(name="ProblemAddressed", type="string")
     * @var string
     */
    private $problem_addressed;

    public static $allowed_fields = array
    (
        'id',
        'title',
        'description',
        'start_date',
        'end_date',
        'location_id',
        'summit_id',
        'type_id',
        'class_name',
        'track_id',
        'moderator_speaker_id',
        'level',
        'allow_feedback',
        'avg_feedback_rate',
        'is_published',
        'head_count',
        'rsvp_link',
    );

    public static $allowed_relations = array
    (
        'summit_types',
        'sponsors',
        'tags',
        'slides',
        'videos',
        'speakers',
        'links',
    );

    /**
     * @return PresentationSpeaker[]
     */
    public function getSpeakers()
    {
        return $this->speakers;
    }

    public function getSpeakerIds()
    {
        $this->speakers->getKeys();
    }

    public function setFromSpeaker()
    {
        $this->from_speaker = true;
    }

    /**
     * @param array $fields
     * @param array $relations
     * @return array
     */
    public function toArray(array $fields = array(), array $relations = array())
    {
        if(!count($fields)) $fields       = self::$allowed_fields;
        if(!count($relations)) $relations = self::$allowed_relations;

        $values = parent::toArray($fields, $relations);

        if(in_array('speakers', $relations)) {
            if (!$this->from_speaker)
                $values['speakers'] = $this->getSpeakerIds();
        }

        if(in_array('slides', $relations) && $this->hasSlides())
        {
            $slides = array();
            foreach ($this->slides() as $s) {
                array_push($slides, $s->toArray());
            }
            $values['slides'] = $slides;
        }

        if(in_array('links', $relations) && $this->hasLinks())
        {
            $links = array();
            foreach ($this->links() as $l) {
                array_push($links, $l->toArray());
            }
            $values['links'] = $links;
        }

        if(in_array('videos', $relations) && $this->hasVideos())
        {
            $videos = array();
            foreach ($this->videos() as $v) {
                array_push($videos, $v->toArray());
            }
            $values['videos'] = $videos;
        }

        return $values;
    }
    /**
     * @return PresentationVideo[]
     */
    public function getVideos()
    {
        return $this->materials->filter(function( $element) { return $element instanceof PresentationVideo; });
    }

    /**
     * @param PresentationVideo $video
     * @return $this
     */
    public function addVideo(PresentationVideo $video){
        $this->materials->add($video);
        $video->setPresentation($this);
    }

     /**
     * @return bool
     */
    public function hasVideos(){
        return $this->videos()->count() > 0;
    }

    /**
     * @return PresentationSlide[]
     */
    public function getSlides()
    {
        return $this->materials->filter(function( $element) { return $element instanceof PresentationSlide; });
    }

    /**
     * @param PresentationSlide $slide
     * @return $this
     */
    public function addSlide(PresentationSlide $slide){
        $this->materials->add($slide);
        $slide->setPresentation($this);
    }

    /**
     * @return bool
     */
    public function hasSlides(){
        return $this->slides()->count() > 0;
    }

    /**
     * @return PresentationLink[]
     */
    public function getLinks(){
        return $this->materials->filter(function($element) { return $element instanceof PresentationLink; });
    }

    /**
     * @return bool
     */
    public function hasLinks(){
        return $this->links()->count() > 0;
    }

    /**
     * @param PresentationLink $link
     * @return $this
     */
    public function addLink(PresentationLink $link){
        $this->materials->add($link);
        $link->setPresentation($this);
    }

     /**
     * @param SummitEvent $event
     * @return Presentation
     */
    public static function toPresentation(SummitEvent $event){
        $presentation  = new Presentation();
        $attributes    = $event->getAttributes();
        $presentation->setRawAttributes($attributes);
        return $presentation;
    }
}
