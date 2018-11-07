<?php namespace App\Models\ResourceServer;
/**
 * Copyright 2017 OpenStack Foundation
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
 * @ORM\Entity(repositoryClass="repositories\resource_server\DoctrineEndPointRateLimitByIPRepository")
 * @ORM\Table(name="ip_rate_limit_routes")
 * Class EndPointRateLimitByIP
 * @package App\Models\ResourceServer
 */
class EndPointRateLimitByIP extends ResourceServerEntity
{

    /**
     * @ORM\Column(name="ip", type="string")
     * @var string
     */
    private $ip;

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
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

    /**
     * @ORM\Column(name="active", type="boolean")
     * @var bool
     */
    private $active;

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

}