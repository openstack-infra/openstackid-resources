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
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Facades\Config;

/**
 * @ORM\Entity(repositoryClass="repositories\resource_server\DoctrineApiEndpointRepository")
 * @ORM\Table(name="api_endpoints")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="resource_server_region")
 * Class ApiEndpoint
 * @package App\Models\ResourceServer
*/
class ApiEndpoint extends ResourceServerEntity implements IApiEndpoint
{

    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param Api $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * @return ApiScope[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param ApiScope[] $scopes
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
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
     * @return boolean
     */
    public function isAllowCors()
    {
        return $this->allow_cors;
    }

    /**
     * @param boolean $allow_cors
     */
    public function setAllowCors($allow_cors)
    {
        $this->allow_cors = $allow_cors;
    }

    /**
     * @return boolean
     */
    public function isAllowCredentials()
    {
        return $this->allow_credentials;
    }

    /**
     * @param boolean $allow_credentials
     */
    public function setAllowCredentials($allow_credentials)
    {
        $this->allow_credentials = $allow_credentials;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param string $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->http_method;
    }

    /**
     * @param string $http_method
     */
    public function setHttpMethod($http_method)
    {
        $this->http_method = $http_method;
    }
    /**
     * @ORM\ManyToOne(targetEntity="Api", cascade={"persist"})
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id")
     * @var Api
     */
    private $api;

    /**
     * @ORM\ManyToMany(targetEntity="ApiScope")
     * @ORM\JoinTable(name="endpoint_api_scopes",
     *      joinColumns={@ORM\JoinColumn(name="api_endpoint_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="scope_id", referencedColumnName="id")}
     * )
     * @var ApiScope[]
     */
    private $scopes;

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
     * @ORM\Column(name="allow_cors", type="boolean")
     * @var bool
     */
    private $allow_cors;

    /**
     * @ORM\Column(name="allow_credentials", type="boolean")
     * @var bool
     */
    private $allow_credentials;

    /**
     * @ORM\Column(name="route", type="string")
     * @var string
     */
    private $route;

    /**
     * @ORM\Column(name="http_method", type="string")
     * @var string
     */
    private $http_method;

    /**
     * @ORM\Column(name="rate_limit", type="integer")
     * @var int
     */
    private $rate_limit;

    /**
     * @ORM\Column(name="rate_limit_decay", type="integer")
     * @var int
     */
    private $rate_limit_decay;

    /**
     * ApiEndpoint constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->rate_limit = 0;
        $this->scopes     = new ArrayCollection();
    }

    /**
	* @return string
	*/
	public function getScope()
	{
        return CacheFacade::remember
        (
            'endpoint_scopes_'.$this->id,
            Config::get("cache_regions.region_api_scopes_lifetime", 1140),
            function() {
                $scope = '';
                foreach ($this->scopes as $s) {
                    if (!$s->isActive()) {
                        continue;
                    }
                    $scope = $scope . $s->getName() . ' ';
                }
                $scope = trim($scope);
                return $scope;
            }
        );
	}

    /**
     * @param IApiScope $scope
     */
	public function addScope(IApiScope $scope){
        $this->scopes->add($scope);
    }

    /**
     * @return int
     */
    public function getRateLimit()
    {
        return $this->rate_limit;
    }

    /**
     * @param int $rate_limit
     */
    public function setRateLimit($rate_limit)
    {
        $this->rate_limit = $rate_limit;
    }

    /**
     * @return int
     */
    public function getRateLimitDecay()
    {
        return $this->rate_limit_decay;
    }

    /**
     * @param int $rate_limit_decay
     */
    public function setRateLimitDecay($rate_limit_decay)
    {
        $this->rate_limit_decay = $rate_limit_decay;
    }

}