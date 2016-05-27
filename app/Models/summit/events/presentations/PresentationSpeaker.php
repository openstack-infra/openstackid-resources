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

use Doctrine\Common\Collections\Criteria;
use models\main\File;
use models\main\Image;
use models\main\Member;
use models\utils\SilverstripeBaseModel;
use Illuminate\Support\Facades\Config;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="PresentationSpeaker")
 * Class PresentationSpeaker
 * @package models\summit
 */
class PresentationSpeaker extends SilverstripeBaseModel
{

    protected static $array_mappings = array
    (
        'ID' => 'id:json_int',
        'FirstName' => 'first_name:json_string',
        'LastName' => 'last_name:json_string',
        'Title' => 'title:json_string',
        'Bio' => 'bio:json_string',
        'IRCHandle' => 'irc:json_string',
        'TwitterName' => 'twitter:json_string',
        'MemberID' => 'member_id:json_int',
    );

    /**
     * @ORM\ManyToMany(targetEntity="models\summit\Presentation", mappedBy="speakers")
     */
    private $presentations;

    public function __construct()
    {
        $this->presentations = new ArrayCollection;
    }

    /**
     * @param null|int $summit_id
     * @param bool|true $published_ones
     * @return Presentation[]
     */
    public function presentations($summit_id, $published_ones = true)
    {

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('summit.id', intval($summit_id)));

        if ($published_ones) {
            $criteria->andWhere(Criteria::expr()->eq('published', true));
        }

        return $this->presentations->matching($criteria);
    }

    /**
     * @param int $presentation_id
     * @return Presentation
     */
    public function getPresentation($presentation_id)
    {
        return $this->presentations->get($presentation_id);
    }
    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function getPresentationIds($summit_id, $published_ones = true)
    {
        return $this->presentations($summit_id,$published_ones)->getKeys();
    }


    /**
     * @ORM\ManyToOne(targetEntity="models\main\File")
     * @ORM\JoinColumn(name="PhotoID", referencedColumnName="ID")
     * @var File
     */
    protected $photo;

    /**
     * @return File
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="MemberID", referencedColumnName="ID")
     * @var Image
     */
    private $member;

    /**
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }
    /**
     * @param null $summit_id
     * @param bool|true $published_ones
     * @return array
     */
    public function toArray($summit_id = null, $published_ones = true)
    {
        $values = parent::toArray();
        $values['presentations'] = $this->getPresentationIds($summit_id, $published_ones);
        $member = $this->member();
        $values['pic'] = Config::get("server.assets_base_url", 'https://www.openstack.org/') . 'profile_images/speakers/' . $this->ID;
        if (!is_null($member)) {
            $values['gender'] = $member->Gender;
        }
        return $values;
    }

}