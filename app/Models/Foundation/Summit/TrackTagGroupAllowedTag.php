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
use App\Models\Utils\BaseEntity;
use Doctrine\ORM\Cache;
use models\main\Tag;
use models\summit\SummitOwned;
use models\utils\SilverstripeBaseModel;
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="TrackTagGroup_AllowedTags")
 * Class TrackTagGroupAllowedTag
 * @package models\summit\TrackTagGroupAllowedTag
 */
class TrackTagGroupAllowedTag extends BaseEntity
{
    /**
     * @ORM\Column(name="IsDefault", type="boolean")
     * @var boolean
     */
    private $is_default;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Tag")
     * @ORM\JoinColumn(name="TagID", referencedColumnName="ID")
     * @var Tag
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="TrackTagGroup", inversedBy="allowed_tags")
     * @ORM\JoinColumn(name="TrackTagGroupID", referencedColumnName="ID")
     * @var TrackTagGroup
     */
    private $track_tag_group;

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->is_default;
    }

    /**
     * @param bool $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }

    /**
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     */
    public function setTag(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return TrackTagGroup
     */
    public function getTrackTagGroup()
    {
        return $this->track_tag_group;
    }

    /**
     * @param TrackTagGroup $track_tag_group
     */
    public function setTrackTagGroup(TrackTagGroup $track_tag_group)
    {
        $this->track_tag_group = $track_tag_group;
    }
}