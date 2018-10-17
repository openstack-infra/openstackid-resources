<?php namespace models\summit;
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
use Doctrine\ORM\Mapping AS ORM;
use models\utils\SilverstripeBaseModel;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Summit\DoctrineSpeakerActiveInvolvementRepository")
 * @ORM\Table(name="SpeakerActiveInvolvement")
 * Class SpeakerActiveInvolvement
 * @package models\summit
 */
class SpeakerActiveInvolvement extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Involvement", type="string")
     */
    private $involvement;

    /**
     * @ORM\Column(name="IsDefault", type="boolean")
     */
    private $is_default;

    /**
     * @return mixed
     */
    public function getInvolvement()
    {
        return $this->involvement;
    }

    /**
     * @param mixed $involvement
     */
    public function setInvolvement($involvement)
    {
        $this->involvement = $involvement;
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