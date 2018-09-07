<?php namespace App\Models\Foundation\Summit;
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
use Doctrine\Common\Collections\ArrayCollection;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineDefaultTrackTagGroupRepository")
 * @ORM\Table(name="DefaultTrackTagGroup")
 * Class DefaultTrackTagGroup
 * @package models\summit\DefaultTrackTagGroup
 */
class DefaultTrackTagGroup extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="Label", type="string")
     * @var string
     */
    private $label;

    /**
     * @ORM\Column(name="Order", type="integer")
     * @var int
     */
    private $order;

    /**
     * @ORM\Column(name="Mandatory", type="boolean")
     * @var boolean
     */
    private $is_mandatory;

    /**
     * @ORM\OneToMany(targetEntity="DefaultTrackTagGroupAllowedTag", mappedBy="track_tag_group", cascade={"persist"}, orphanRemoval=true)
     * @var DefaultTrackTagGroupAllowedTag[]
     */
    private $allowed_tags;

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
     * @return bool
     */
    public function isMandatory()
    {
        return $this->is_mandatory;
    }

    /**
     * @param bool $is_mandatory
     */
    public function setIsMandatory($is_mandatory)
    {
        $this->is_mandatory = $is_mandatory;
    }

    /**
     * @return DefaultTrackTagGroupAllowedTag[]
     */
    public function getAllowedTags()
    {
        return $this->allowed_tags;
    }

    /**
     * @param DefaultTrackTagGroupAllowedTag[] $allowed_tags
     */
    public function setAllowedTags($allowed_tags)
    {
        $this->allowed_tags = $allowed_tags;
    }

    public function __construct()
    {
        parent::__construct();
        $this->allowed_tags = new ArrayCollection;
    }

}