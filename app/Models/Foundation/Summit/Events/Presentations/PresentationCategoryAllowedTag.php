<?php namespace App\Models\Foundation\Summit\Events\Presentations;
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
use models\main\Tag;
use Doctrine\ORM\Mapping AS ORM;
use models\summit\PresentationCategory;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity()
 * @ORM\Table(name="PresentationCategory_AllowedTags")
 * Class PresentationCategoryAllowedTag
 * @package App\Models\Foundation\Summit\Events\Presentations
 */
class PresentationCategoryAllowedTag extends SilverstripeBaseModel
{
    /**
     * @ORM\ManyToOne(targetEntity="models\main\Tag")
     * @ORM\JoinColumn(name="TagID", referencedColumnName="ID")
     * @var Tag
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="models\summit\PresentationCategory", inversedBy="allowed_tags")
     * @ORM\JoinColumn(name="PresentationCategoryID", referencedColumnName="ID")
     * @var PresentationCategory
     */
    private $track;

    /**
     * @ORM\Column(name="IsDefault", type="boolean")
     * @var boolean
     */
    private $is_default;

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
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return PresentationCategory
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @param PresentationCategory $track
     */
    public function setTrack($track)
    {
        $this->track = $track;
    }

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
}