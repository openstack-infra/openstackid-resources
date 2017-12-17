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

use models\summit\PresentationSpeaker;

/**
 * Class AdminPresentationSpeakerSerializer
 * @package ModelSerializers
 */
final class AdminPresentationSpeakerSerializer extends PresentationSpeakerSerializer
{
    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = [], array $relations = [], array $params = [] )
    {
        if(!count($relations)) $relations  = $this->getAllowedRelations();

        $speaker                           = $this->object;
        if(!$speaker instanceof PresentationSpeaker) return [];

        $values = parent::serialize($expand, $fields, $relations, $params);
        $values['email'] = $speaker->getEmail();
        $summit = isset($params['summit'])? $params['summit']:null;

        if(!is_null($summit)){
            $summit_assistance = $speaker->getAssistanceFor($summit);
            if($summit_assistance){
                $values['summit_assistance'] = SerializerRegistry::getInstance()->getSerializer($summit_assistance)->serialize();
            }
            $registration_code = $speaker->getPromoCodeFor($summit);
            if($registration_code){
                $values['registration_code'] = SerializerRegistry::getInstance()->getSerializer($registration_code)->serialize();
            }
        }
        return $values;
    }
}