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

use models\main\Member;
use Illuminate\Support\Facades\Config;

/**
 * Class AbstractMemberSerializer
 * @package ModelSerializers
 */
class AbstractMemberSerializer extends SilverStripeSerializer
{
    protected static $array_mappings = [

        'FirstName'       => 'first_name:json_string',
        'LastName'        => 'last_name:json_string',
        'Gender'          => 'gender:json_string',
        'GitHubUser'      => 'github_user:json_string',
        'Bio'             => 'bio:json_string',
        'LinkedInProfile' => 'linked_in:json_string',
        'IrcHandle'       => 'irc:json_string',
        'TwitterHandle'   => 'twitter:json_string',
        'State'           => 'state:json_string',
        'Country'         => 'country:json_string',
        'Active'          => 'active:json_boolean',
        'EmailVerified'   => 'email_verified:json_boolean',
    ];

    protected static $allowed_relations = [
        'groups',
        'affiliations',
        'ccla_teams',
    ];

    /**
     * @param null $expand
     * @param array $fields
     * @param array $relations
     * @param array $params
     * @return array
     */
    public function serialize($expand = null, array $fields = array(), array $relations = array(), array $params = array())
    {
        $member  = $this->object;
        if(!$member instanceof Member) return [];

        if(!count($relations)) $relations = $this->getAllowedRelations();

        $values           = parent::serialize($expand, $fields, $relations, $params);
        $values['pic']    = $member->getProfilePhotoUrl();

        if(in_array('groups', $relations))
            $values['groups'] = $member->getGroupsIds();

        if(in_array('ccla_teams', $relations))
            $values['ccla_teams'] = $member->getCCLATeamsIds();

        if(in_array('affiliations', $relations)){
            $res = [];
            foreach ($member->getCurrentAffiliations() as $affiliation){
                $res[] = SerializerRegistry::getInstance()
                    ->getSerializer($affiliation)
                    ->serialize('organization');
            }
            $values['affiliations'] = $res;
        }

        if (!empty($expand)) {
            $exp_expand = explode(',', $expand);
            foreach ($exp_expand as $relation) {
                switch (trim($relation)) {
                   case 'groups': {
                        if(!in_array('groups', $relations)) break;
                        $groups = [];
                        unset($values['groups']);
                        foreach ($member->getGroups() as $g) {
                            $groups[] = SerializerRegistry::getInstance()->getSerializer($g)->serialize(null, [], ['none']);
                        }
                        $values['groups'] = $groups;
                    }
                    break;
                    case 'ccla_teams': {
                        if(!in_array('ccla_teams', $relations)) break;
                        $teams = [];
                        unset($values['ccla_teams']);
                        foreach ($member->getCCLATeams() as $t) {
                            $teams[] = SerializerRegistry::getInstance()->getSerializer($t)->serialize('company', [], ['none']);
                        }
                        $values['ccla_teams'] = $teams;
                    }
                    break;
                    case 'all_affiliations':
                    {
                        $res = [];
                        foreach ($member->getAllAffiliations() as $affiliation){
                            $res[] = SerializerRegistry::getInstance()
                                ->getSerializer($affiliation)
                                ->serialize('organization');
                        }
                        $values['affiliations'] = $res;
                    }
                    break;
                }
            }
        }
        return $values;
    }
}