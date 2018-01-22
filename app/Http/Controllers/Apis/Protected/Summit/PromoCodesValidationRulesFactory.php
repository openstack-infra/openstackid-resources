<?php namespace App\Http\Controllers;
/**
 * Copyright 2018 OpenStack Foundation
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
use App\Models\Foundation\Summit\PromoCodes\PromoCodesValidClasses;
use models\exceptions\ValidationException;
use models\summit\MemberSummitRegistrationPromoCode;
use models\summit\SpeakerSummitRegistrationPromoCode;
use models\summit\SponsorSummitRegistrationPromoCode;
/**
 * Class PromoCodesValidationRulesFactory
 * @package App\Http\Controllers
 */
final class PromoCodesValidationRulesFactory
{
    /**
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public static function buildAddRules(array $data){
        if(!isset($data['class_name']))
            throw new ValidationException("class_name parameter is mandatory");

        $class_name = trim($data['class_name']);

        if(!in_array($class_name, PromoCodesValidClasses::$valid_class_names)){
            throw new ValidationException(
                sprintf
                (
                    "class_name param has an invalid value ( valid values are %s",
                    implode(", ", PromoCodesValidClasses::$valid_class_names)
                )
            );
        }

        $base_rules = [
            'code' =>  'required|string',
        ];

        $specific_rules = [];

        switch ($class_name){
            case MemberSummitRegistrationPromoCode::ClassName:{
                $specific_rules = [
                    'first_name' => 'required_without:owner_id|string',
                    'last_name'  => 'required_without:owner_id|string',
                    'email'      => 'required_without:owner_id|email|max:254',
                    'type'       => 'required|string|in:'.join(",",MemberSummitRegistrationPromoCode::$valid_type_values),
                    'owner_id'   => 'required_without:first_name,last_name,email|integer'
                ];
            }
            break;
            case SpeakerSummitRegistrationPromoCode::ClassName:
            {
                $specific_rules = [
                    'type'       => 'required|string|in:'.join(",",SpeakerSummitRegistrationPromoCode::$valid_type_values),
                    'speaker_id' => 'required|integer'
                ];
            }
            break;
            case SponsorSummitRegistrationPromoCode::ClassName:
            {
                $specific_rules = [
                    'sponsor_id' => 'required|integer'
                ];
            }
            break;
        }

        return array_merge($base_rules, $specific_rules);
    }
}