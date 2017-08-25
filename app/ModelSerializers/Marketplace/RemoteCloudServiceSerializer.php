<?php namespace App\ModelSerializers\Marketplace;
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

/**
 * Class RemoteCloudServiceSerializer
 * @package App\ModelSerializers\Marketplace
 */
final class RemoteCloudServiceSerializer extends OpenStackImplementationSerializer
{
    /**
     * @var array
     */
    protected static $array_mappings = [
        'HardwareSpec'          => 'hardware_spec:json_string',
        'PricingModels'         => 'pricing_models:json_string',
        'PublishedSla'          => 'published_sla:json_string',
        'VendorManagedUpgrades' => 'is_vendor_managed_upgrades:json_boolean',
    ];
}