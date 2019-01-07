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
use App\Models\Foundation\Main\IOrderable;
use Doctrine\Common\Collections\ArrayCollection;
use models\main\Tag;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(
 *          name="summit",
 *          inversedBy="track_tag_groups"
 *     )
 * })
 * @ORM\Table(name="TrackTagGroup")
 * Class TrackTagGroup
 * @package models\summit\TrackTagGroup
 */
class TrackTagGroup extends SilverstripeBaseModel implements IOrderable
{
    use SummitOwned;

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
     * @ORM\Column(name="`Order`", type="integer")
     * @var int
     */
    private $order;

    /**
     * @ORM\Column(name="Mandatory", type="boolean")
     * @var boolean
     */
    private $is_mandatory;

    /**
     * @ORM\OneToMany(targetEntity="TrackTagGroupAllowedTag", mappedBy="track_tag_group", cascade={"persist"}, orphanRemoval=true)
     * @var TrackTagGroupAllowedTag[]
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
     * @return TrackTagGroupAllowedTag[]
     */
    public function getAllowedTags()
    {
        return $this->allowed_tags;
    }

    /**
     * @param TrackTagGroupAllowedTag[] $allowed_tags
     */
    public function setAllowedTags($allowed_tags)
    {
        $this->allowed_tags = $allowed_tags;
    }

    public function __construct()
    {
        parent::__construct();
        $this->allowed_tags = new ArrayCollection;
        $this->is_mandatory = false;
    }

    public function clearAllowedTags()
    {
        $this->allowed_tags->clear();
    }

    /**
     * @param Tag $tag
     * @param bool $is_default
     */
    public function addTag(Tag $tag, $is_default = false)
    {
        $allowed_tag = new TrackTagGroupAllowedTag();
        $allowed_tag->setTag($tag);
        $allowed_tag->setTrackTagGroup($this);
        $allowed_tag->setIsDefault($is_default);
        $this->allowed_tags->add($allowed_tag);
    }

}