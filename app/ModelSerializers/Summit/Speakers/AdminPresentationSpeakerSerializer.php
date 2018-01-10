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

        $values          = parent::serialize($expand, $fields, $relations, $params);
        $values['email'] = $speaker->getEmail();
        $summit          = isset($params['summit'])? $params['summit']:null;

        if(!is_null($summit)){
            $summit_assistance = $speaker->getAssistanceFor($summit);
            if($summit_assistance){
                $values['summit_assistance'] = SerializerRegistry::getInstance()->getSerializer($summit_assistance)->serialize();
            }
            $registration_code = $speaker->getPromoCodeFor($summit);
            if($registration_code){
                $values['registration_code'] = SerializerRegistry::getInstance()->getSerializer($registration_code)->serialize();
            }

            $values['all_presentations']           = $speaker->getPresentationIds($summit->getId() ,false);
            $values['all_moderated_presentations'] = $speaker->getModeratedPresentationIds($summit->getId(), false);
        }
        else{
            // get all summits info
            $summit_assistances = [];
            foreach ($speaker->getSummitAssistances() as $assistance){
                $summit_assistances[] = SerializerRegistry::getInstance()->getSerializer($assistance)->serialize();
            }
            $values['summit_assistances'] = $summit_assistances;

            $registration_codes = [];
            foreach ($speaker->getPromoCodes() as $promo_code){
                $registration_codes[] = SerializerRegistry::getInstance()->getSerializer($promo_code)->serialize();
            }
            $values['registration_codes'] = $registration_codes;

            $values['all_presentations']           = $speaker->getAllPresentationIds(false);
            $values['all_moderated_presentations'] = $speaker->getAllModeratedPresentationIds( false);

        }

        $languages = [];
        foreach ($speaker->getLanguages() as $language){
            $languages[] = SerializerRegistry::getInstance()->getSerializer($language)->serialize();
        }
        $values['languages'] = $languages;

        $other_presentation_links = [];
        foreach ($speaker->getOtherPresentationLinks() as $link){
            $other_presentation_links[] = SerializerRegistry::getInstance()->getSerializer($link)->serialize();
        }
        $values['other_presentation_links'] = $other_presentation_links;

        $areas_of_expertise = [];
        foreach ($speaker->getAreasOfExpertise() as $exp){
            $areas_of_expertise[] = SerializerRegistry::getInstance()->getSerializer($exp)->serialize();
        }
        $values['areas_of_expertise'] = $areas_of_expertise;

        $travel_preferences = [];
        foreach ($speaker->getTravelPreferences() as $tp){
            $travel_preferences[] = SerializerRegistry::getInstance()->getSerializer($tp)->serialize();
        }
        $values['travel_preferences'] = $travel_preferences;

        $active_involvements = [];
        foreach ($speaker->getActiveInvolvements() as $ai){
            $active_involvements[] = SerializerRegistry::getInstance()->getSerializer($ai)->serialize();
        }
        $values['active_involvements'] = $active_involvements;

        $organizational_roles = [];
        foreach ($speaker->getOrganizationalRoles() as $or){
            $organizational_roles[] = SerializerRegistry::getInstance()->getSerializer($or)->serialize();
        }
        $values['organizational_roles'] = $organizational_roles;

        if (!empty($expand)) {
            foreach (explode(',', $expand) as $relation) {
                switch (trim($relation)) {
                    case 'presentations': {
                        $presentations = [];
                        $moderated_presentations = [];
                        if(is_null($summit)){

                            foreach ($speaker->getAllPresentations( false) as $p) {
                                $presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                            }

                            foreach ($speaker->getAllModeratedPresentations(false) as $p) {
                                $moderated_presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                            }
                        }
                        else{
                            foreach ($speaker->getPresentations($summit->getId(), false) as $p) {
                                $presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                            }

                            foreach ($speaker->getModeratedPresentations($summit->getId(), false) as $p) {
                                $moderated_presentations[] = SerializerRegistry::getInstance()->getSerializer($p)->serialize();
                            }
                        }

                        $values['all_presentations']           = $presentations;
                        $values['all_moderated_presentations'] = $moderated_presentations;
                    }
                    break;
                }
            }
        }
        return $values;
    }
}