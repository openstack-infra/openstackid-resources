<?php namespace ModelSerializers;
/**
 * Copyright 2016 OpenStack Foundation
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
use models\summit\PresentationSpeaker;

/**
 * Class PresentationSpeakerSerializer
 * @package ModelSerializers
 */
class PresentationSpeakerSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [
        'FirstName'   => 'first_name:json_string',
        'LastName'    => 'last_name:json_string',
        'Title'       => 'title:json_string',
        'Bio'         => 'bio:json_string',
        'IRCHandle'   => 'irc:json_string',
        'TwitterName' => 'twitter:json_string',
    ];

    protected static $allowed_relations = [
        'member',
    ];

    /**
     * @param PresentationSpeaker $speaker
     * @return null|string|string[]
     */
    protected function getSpeakerEmail(PresentationSpeaker $speaker){
        $speaker_email = $speaker->getEmail();
        return preg_replace('/(?<=.).(?=.*.)/u','*', $speaker_email);
    }

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

        $values                            = parent::serialize($expand, $fields, $relations, $params);
        $values['email']                   = $this->getSpeakerEmail($speaker);
        $summit_id                         = isset($params['summit_id'])? intval($params['summit_id']):null;
        $published                         = isset($params['published'])? intval($params['published']):true;
        if(!is_null($summit_id)) {
            $values['presentations']           = $speaker->getPresentationIds($summit_id, $published);
            $values['moderated_presentations'] = $speaker->getModeratedPresentationIds($summit_id, $published);
        }

        $values['pic']                     = Config::get("server.assets_base_url", 'https://www.openstack.org/') . 'profile_images/speakers/' . $speaker->getId();

        if (in_array('member', $relations) && $speaker->hasMember())
        {
            $member              = $speaker->getMember();
            $values['gender']    = $member->getGender();
            $values['member_id'] = intval($member->getId());
        }

        if(empty($values['first_name']) || empty($values['last_name'])){

            $first_name = '';
            $last_name  = '';
            if ($speaker->hasMember())
            {
                $member     = $speaker->getMember();
                $first_name = $member->getFirstName();
                $last_name  = $member->getLastName();
            }
            $values['first_name'] = $first_name;
            $values['last_name']  = $last_name;
        }


        $affiliations = [];
        if($speaker->hasMember()) {
            $member = $speaker->getMember();
            foreach ($member->getCurrentAffiliations() as $affiliation) {
                $affiliations[] = SerializerRegistry::getInstance()->getSerializer($affiliation)->serialize('organization');
            }
        }
        $values['affiliations'] = $affiliations;

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'presentations': {
                        $presentations = [];
                        foreach ($speaker->getPresentations($summit_id, $published) as $p) {
                            $presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                        }
                        $values['presentations'] = $presentations;

                        $moderated_presentations = [];
                        foreach ($speaker->getModeratedPresentations($summit_id, $published) as $p) {
                            $moderated_presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                        }
                        $values['moderated_presentations'] = $presentations;
                    }
                    break;
                    case 'member': {
                       if($speaker->hasMember()){
                           unset($values['member_id']);
                           $values['member'] =  SerializerRegistry::getInstance()->getSerializer($speaker->getMember())->serialize();
                       }
                    }
                    break;
                }
            }
        }

        return $values;
    }
}