<?php namespace App\Models\Foundation\Marketplace;
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
use Doctrine\ORM\Mapping AS ORM;
/**
 * @ORM\Entity(repositoryClass="App\Repositories\Marketplace\DoctrineApplianceRepository")
 * @ORM\Table(name="Appliance")
 * Class Appliance
 * @package App\Models\Foundation\Marketplace
 */
class Appliance extends OpenStackImplementation
{
    /**
     * @ORM\Column(name="Type", type="string")
     * @var string
     */
    protected $type;
}