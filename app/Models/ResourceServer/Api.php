<?php namespace App\Models\ResourceServer;
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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="apis")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="resource_server_region")
 * Class Api
 * @package App\Models\ResourceServer
*/
class Api extends ResourceServerEntity implements IApi
{

    /**
     * @ORM\OneToMany(targetEntity="ApiScope", mappedBy="api", cascade={"persist"}, orphanRemoval=true)
     */
    private $scopes;

    /**
     * @ORM\OneToMany(targetEntity="ApiEndpoint", mappedBy="api", cascade={"persist"})
     */
    private $endpoints;

    /**
     * @ORM\Column(name="name", type="string")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="description", type="string")
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(name="active", type="boolean")
     * @var bool
     */
    private $active;

    /**
     * Api constructor.
     */
	public function __construct()
    {
        parent::__construct();
        $this->scopes    = new ArrayCollection();
        $this->endpoints = new ArrayCollection();
    }

    /**
     * @return ApiScope[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param mixed $scopes
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @return ApiEndpoint[]
     */
    public function getEndpoints()
    {
        return $this->endpoints;
    }

    /**
     * @param mixed $endpoints
     */
    public function setEndpoints($endpoints)
    {
        $this->endpoints = $endpoints;
    }

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

	/**
	* @return string
	*/
	public function getScope()
	{
		$scope = '';
		foreach ($this->getScopes() as $s)
		{
			if (!$s->isActive())
			{
				continue;
			}
			$scope = $scope .$s->getName().' ';
		}
		$scope = trim($scope);
		return $scope;
	}

}