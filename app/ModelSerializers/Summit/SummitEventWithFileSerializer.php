<?php namespace ModelSerializers;

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

use Illuminate\Support\Facades\Config;
use models\summit\SummitEventWithFile;

/**
 * Class SummitEventWithFileSerializer
 * @package ModelSerializers
 */
final class SummitEventWithFileSerializer extends SummitEventSerializer
{
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {

        $event = $this->object;

        if (!$event instanceof SummitEventWithFile) return [];

        $values = parent::serialize($expand, $fields, $relations, $params);

        $values['attachment'] = $event->hasAttachment()? Config::get("server.assets_base_url", 'https://www.openstack.org/') . $event->getAttachment()->getFilename() : null;

        return $values;
    }
}