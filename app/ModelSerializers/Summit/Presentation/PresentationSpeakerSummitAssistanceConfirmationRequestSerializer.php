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
use models\summit\PresentationSpeakerSummitAssistanceConfirmationRequest;
/**
 * Class PresentationSpeakerSummitAssistanceConfirmationRequestSerializer
 * @package ModelSerializers
 */
final class PresentationSpeakerSummitAssistanceConfirmationRequestSerializer
    extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'OnSitePhone'      => 'on_site_phone:json_string',
        'Registered'       => 'registered:json_boolean',
        'Confirmed'        => 'is_confirmed:json_boolean',
        'CheckedIn'        => 'checked_in:json_boolean',
        'SummitId'         => 'summit_id:json_int',
        'SpeakerId'        => 'speaker_id:json_int',
        'ConfirmationDate' => 'confirmation_date:datetime_epoch',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array() )
    {
        if(!count($relations)) $relations = $this->getAllowedRelations();

        $request = $this->object;

        if(!$request instanceof PresentationSpeakerSummitAssistanceConfirmationRequest) return [];

        $serializer_type = SerializerRegistry::SerializerType_Public;

        if(isset($params['serializer_type']))
            $serializer_type = $params['serializer_type'];

        $values = parent::serialize($expand, $fields, $relations, $params);

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'speaker': {
                        if(isset($values['speaker_id']) && intval($values['speaker_id']) > 0 ){
                            unset($values['speaker_id']);
                            $values['speaker'] = SerializerRegistry::getInstance()->getSerializer($request->getSpeaker(), $serializer_type)->serialize();
                        }
                    }
                    break;
                }
            }
        }
        return $values;
    }
}