<?php namespace models\main;
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
use models\utils\SilverstripeBaseModel;
use Illuminate\Support\Facades\Config;
/**
 * @ORM\Entity(repositoryClass="repositories\main\DoctrineFolderRepository")
 * @ORM\Table(name="File")
 * Class File
 * @package models\main
 */
class File extends SilverstripeBaseModel
{
    /**
     * @ORM\Column(name="Name", type="string")
     */
    private $name;

    /**
     * @ORM\Column(name="Title", type="string")
     */
    private $title;

    /**
     * @ORM\Column(name="ClassName", type="string")
     */
    private $class_name;

    /**
     * @ORM\Column(name="Content", type="string")
     */
    private $content;

    /**
     * @ORM\Column(name="Filename", type="string")
     */
    private $filename;

    /**
     * @ORM\Column(name="ShowInSearch", type="boolean")
     * @var bool
     */
    private $show_in_search;

    /**
     * @ORM\Column(name="CloudStatus", type="string")
     */
    private $cloud_status;

    /**
     * @ORM\Column(name="CloudSize", type="integer")
     */
    private $cloud_size;

    /**
     * @ORM\Column(name="CloudMetaJson", type="string")
     */
    private $cloud_metajson;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\File")
     * @ORM\JoinColumn(name="ParentID", referencedColumnName="ID")
     * @var File
     */
    private $parent;

    /**
     * @ORM\ManyToOne(targetEntity="models\main\Member")
     * @ORM\JoinColumn(name="OwnerID", referencedColumnName="ID")
     * @var Member
     */
    private $owner;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return bool
     */
    public function isShowInSearch()
    {
        return $this->show_in_search;
    }

    /**
     * @param bool $show_in_search
     */
    public function setShowInSearch($show_in_search)
    {
        $this->show_in_search = $show_in_search;
    }

    /**
     * @return File
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param File $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Member $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function __construct()
    {
        parent::__construct();
        $this->class_name     = 'CloudFile';
        $this->show_in_search = true;
        $this->cloud_metajson = "";
        $this->cloud_status = "Local";
        $this->cloud_size = 0;
    }

    public function setImage(){
        $this->class_name = 'CloudImage';
    }

    public function setFolder(){
        $this->class_name = 'Folder';
    }

    /**
     * @return string
     */
    public function getCloudStatus()
    {
        return $this->cloud_status;
    }

    /**
     * @param string $cloud_status
     */
    public function setCloudStatus($cloud_status): void
    {
        $this->cloud_status = $cloud_status;
    }

    /**
     * @return int
     */
    public function getCloudSize()
    {
        return $this->cloud_size;
    }

    /**
     * @param int $cloud_size
     */
    public function setCloudSize($cloud_size): void
    {
        $this->cloud_size = $cloud_size;
    }

    /**
     * @param string $cloud_metajson
     */
    public function setCloudMetaJSON($cloud_metajson): void
    {
        $this->cloud_metajson = $cloud_metajson;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $local = Config::get("server.assets_base_url", 'https://www.openstack.org/').$this->getFilename();
        return $this->cloud_status == 'Live' ? $this->getCloudLink() : $local;
    }

    /**
     * @return string
     */
    public function getRelativeLinkFor()
    {
        $fn = $this->getFilename();
        return trim(str_replace("assets", '', $fn), '/');
    }

    /**
     * @return string
     */
    public function getCloudLink()
    {
        return
            sprintf("%s/%s/%s",
                Config::get("cloudstorage.base_url") ,
                Config::get("cloudstorage.assets_container"),
                $this->getRelativeLinkFor());
    }

    /**
     * @param string $imageRelativePath
     * @return string
     */
    public static function getCloudLinkForImages(string $imageRelativePath):string {
        return
            sprintf("%s/%s/%s",
                Config::get("cloudstorage.base_url") ,
                Config::get("cloudstorage.images_container"),
                $imageRelativePath
            );
    }

    /**
     * @param string|array $key - passing an array as the first argument replaces the meta data entirely
     * @param mixed        $val
     * @return File - chainable
     */
    public function setCloudMeta($key, $val = null)
    {
        if (is_array($key)) {
            $data = $key;
        } else {
            $data = $this->getCloudMetaJSON();
            $data[$key] = $val;
        }

        $this->cloud_metajson = json_encode($data);
        return $this;
    }


    /**
     * @param string $key [optional] - if not present returns the whole array
     * @return array
     */
    public function getCloudMetaJSON($key = null)
    {
        $data = json_decode($this->cloud_metajson, true);
        if (empty($data) || !is_array($data)) {
            $data = array();
        }

        if (!empty($key)) {
            return isset($data[$key]) ? $data[$key] : null;
        } else {
            return $data;
        }
    }

}